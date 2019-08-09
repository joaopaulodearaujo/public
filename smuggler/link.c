#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include "global.h"
#include "link.h"
#include "physical.h"

extern FILE *debug_stream;
statistics_t statistics = {0, 0, 0};
static uint8_t sequence = 0x0;
static uint8_t next_ack_sequence = 0x80;

void reset_sequence() {

	sequence = 0x0;
	next_ack_sequence = 0x80;
	
}

void data_request(const char *destination_address, const lsdu_t *lsdu) {
	
	frame_t frame;
	input_crc_t *input_crc;
	unsigned int i;
	unsigned int input_crc_length;
	uint8_t next_data_sequence = 0xFF;
	struct sockaddr_in destination_struct;
	struct sockaddr_in source_struct;
	
	/* common frame fields for an application request */
	frame.delimiter = DELIMITER;
	frame.type = DATA_FRAME;
	
	inet_aton(destination_address, &destination_struct.sin_addr);
	frame.destination_address = destination_struct.sin_addr.s_addr;
	
	inet_aton("127.0.0.1", &source_struct.sin_addr);
	frame.source_address = source_struct.sin_addr.s_addr;
	
	/* variable fields, depending on the last transmission and payload size */
	frame.sequence = sequence ^ 0x80;
	frame.length = lsdu -> size;
	
	frame.payload = VOID(frame.length);
	memcpy(frame.payload, lsdu -> data, frame.length);

	/* Debug our frame */
	fprintf(debug_stream, "Output - LL[Delimiter]: %i\n", frame.delimiter);
	fprintf(debug_stream, "Output - LL[Type]: %i\n", frame.type);
	fprintf(debug_stream, "Output - LL[Destination]: %i == %s\n", frame.destination_address, inet_ntoa(destination_struct.sin_addr));
	fprintf(debug_stream, "Output - LL[Source]: %i == %s\n", frame.source_address, inet_ntoa(source_struct.sin_addr));
	fprintf(debug_stream, "Output - LL[Sequence]: %i\n", frame.sequence);
	fprintf(debug_stream, "Output - LL[Length]: %i\n", frame.length);
	fprintf(debug_stream, "Output - LL[Payload]: ");
	
	for (i = 0; i < frame.length; i++) {

		fprintf(debug_stream, "%i ", ((uint8_t *) frame.payload)[i]);

	}

	fprintf(debug_stream, "\n");
	
	while (next_data_sequence != sequence) {

		input_crc_length = 0;
		input_crc = NULL;

		send_byte(&frame.delimiter, sizeof(frame.delimiter), FALSE, NULL, NULL);
		send_byte(&frame.type, sizeof(frame.type), TRUE, &input_crc, &input_crc_length);
		send_byte(&frame.length, sizeof(frame.length), TRUE, &input_crc, &input_crc_length);
		send_byte(&frame.sequence, sizeof(frame.sequence), TRUE, &input_crc, &input_crc_length);
		send_byte(&frame.destination_address, sizeof(frame.destination_address), TRUE, &input_crc, &input_crc_length);
		send_byte(&frame.source_address, sizeof(frame.source_address), TRUE, &input_crc, &input_crc_length);
		send_byte(frame.payload, frame.length, TRUE, &input_crc, &input_crc_length);
		
		next_data_sequence = frame.sequence ^ 0x1;

		crcInit();
		frame.crc = crcFast(input_crc, input_crc_length);
		
		fprintf(debug_stream, "Output - LL[CRC]: %i\n", frame.crc);

		send_byte(&frame.crc, sizeof(frame.crc), FALSE, NULL, NULL);
		
		fprintf(debug_stream, "\n");
		
		statistics.sent++;
		
		usleep(TIMEOUT);
		
	}

}

void data_indication(char *destination_address, char *source_address, lsdu_t *lsdu) {

	int i;
	void *byte;
	void *stream = NULL;
	int stream_length = 0;
	int stream_max_length = -1;
	uint8_t type = DATA_FRAME;
	uint8_t payload_length = 0;
	ack_t ack;
	crc_t generated_crc;
	crc_t received_crc = 0;
	struct in_addr destination_struct;
	struct in_addr source_struct;

	byte = VOID(1);

	for (;;) {
		
		f_data_indication(byte);
		
		stream = realloc(stream, stream_length + 1);

		memcpy((stream + stream_length), byte, 1);
		
		if (stream_length == 1) {

			type = *(uint8_t *) byte;
			
		} else if (stream_length == 2) {
			
			payload_length = *((uint8_t *) byte);

			stream_max_length = (type == DATA_FRAME) ? DATA_STREAM_BASE_SIZE + payload_length : ACK_STREAM_BASE_SIZE;

		} 
		
		stream_length++;
		
		if (stream_length == stream_max_length) {

			break;

		}
		
	}
	
	fprintf(debug_stream, "Input - LL[");
	
	if (type == ACK_FRAME) {

		fprintf(debug_stream, "ACK_stream]: ");

	} else {

		fprintf(debug_stream, "data_stream]: ");

	}
	
	for (i = 0; i < stream_length; i++) {
	
		fprintf(debug_stream, "%i ", ((uint8_t *) stream)[i]);

	}
	
	fprintf(debug_stream, "\n\n");
	
	fflush(debug_stream);
	
	statistics.received++;
	
	if (type == DATA_FRAME) {
	
		crcInit();
		generated_crc = crcFast(stream + 1, 11 + payload_length);
		
		memcpy(&received_crc, stream + stream_length - 2, 2);

		if (received_crc == generated_crc && ((uint8_t *) stream)[3] == next_ack_sequence) {
			
			next_ack_sequence ^= 0x1 ^ 0x80;
		
			memcpy(&destination_struct.s_addr, stream + 4, 4);
			strcpy(destination_address, inet_ntoa(destination_struct));
			
			memcpy(&source_struct.s_addr, stream + 8, 4);
			strcpy(source_address, inet_ntoa(source_struct));
			
			lsdu -> data = MALLOC(payload_length, lsdu_t);
			
			memcpy(lsdu -> data, stream + 12, payload_length);
			lsdu -> size = payload_length;

			ack.delimiter = DELIMITER;
			ack.type = ACK_FRAME;
			ack.sequence = ((uint8_t *) stream)[3] ^ 0x1;
			ack.destination_address = destination_struct.s_addr;
			ack.source_address = source_struct.s_addr;
			
			send_byte(&ack.delimiter, sizeof(ack.delimiter), FALSE, NULL, NULL);
			send_byte(&ack.type, sizeof(ack.type), FALSE, NULL, NULL);
			send_byte(&ack.sequence, sizeof(ack.sequence), FALSE, NULL, NULL);
			send_byte(&ack.source_address, sizeof(ack.source_address), FALSE, NULL, NULL);
			send_byte(&ack.destination_address, sizeof(ack.destination_address), FALSE, NULL, NULL);

		} else {
			
			statistics.crc_error++;

			lsdu -> data = NULL;
			lsdu -> size = 0;

		}
		
		received_crc = 0;

	} else {

		sequence = ((uint8_t *) stream)[2];

		lsdu -> data = NULL;
		lsdu -> size = 0;

	}

}

void send_byte (const void *data, uint8_t size, bool_t crc, input_crc_t **input_crc, unsigned int *input_crc_length) {
	
	uint8_t i;
	
	if (crc) {

		*input_crc = (input_crc_t *) realloc(*input_crc, *input_crc_length + size);

	}
	
	for (i = 0; i < size; i++) {

		f_data_request(((uint8_t *) data)[i]);

		if (crc) {

			memcpy(*input_crc + *input_crc_length + i, &((input_crc_t *) data)[i], 1);

		}

	}
	
	if (crc) {

		*input_crc_length += size;

	}

}
