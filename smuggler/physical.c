#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <time.h>
#include <arpa/inet.h>
#include <sys/types.h>
#include <netinet/in.h>
#include <sys/socket.h>
#include "global.h"
#include "physical.h"

extern FILE *debug_stream;
uint8_t error_probability;
static int socket_fd = 0;
static struct sockaddr_in client_struct;

void f_data_request(uint8_t byte) {
	
	static uint32_t i;
	static uint8_t payload_length;
	static uint8_t type = DATA_FRAME;
	static size_t stream_max_length;
	static size_t stream_length = 0;
	static void *stream;
	static struct sockaddr_in destination_struct;
	uint32_t destination_address = 0;
	uint8_t random;
	
	if (stream_length == 1) {

		type = byte;
		
	} else if (stream_length == 2) {
		
		payload_length = byte;
		
		stream_max_length = (type == DATA_FRAME) ? DATA_STREAM_BASE_SIZE + payload_length : ACK_STREAM_BASE_SIZE;

	} 
	
	stream = realloc(stream, stream_length + 1);	
	
	memcpy((stream + stream_length), &byte, 1);	
	
	stream_length++;
	
	if (stream_length == stream_max_length) {
	
		if (rand() % 100 < error_probability) {
			
			random = rand() % (stream_length - 1);
			
			if (random < 3) {

				random += 3;

			}
			
			fprintf(debug_stream, "Output - Physical[error]: (%i%%) %i -> ", error_probability, ((uint8_t *) stream)[random]);

			((uint8_t *) stream)[random] ^= ERROR_MASK;
			
			fprintf(debug_stream, "%i\n", ((uint8_t *) stream)[random]);

		}
	
		fprintf(debug_stream, "Output - Physical[");
		
		if (type == ACK_FRAME) {
			
			fprintf(debug_stream, "ACK_stream]: ");
			
		} else {

			fprintf(debug_stream, "data_stream]: ");

		}

		for (i = 0; i < stream_max_length; i++) {
		
			fprintf(debug_stream, "%i ", ((uint8_t *) stream)[i]);

		}
		
		fprintf(debug_stream, "\n");
		
		if (type == ACK_FRAME) {
			fprintf(debug_stream, "\n");
		}
		
		fflush(debug_stream);
		
		if (type == DATA_FRAME) {
		
			memcpy(&destination_address, stream + 4, 4);
			
			memset(&destination_struct, 0, sizeof(destination_struct));
			
			destination_struct.sin_family = AF_INET;
			destination_struct.sin_addr.s_addr = destination_address;
			destination_struct.sin_port = htons(PORT);

		} else {

			memcpy(&destination_address, stream + 3, 4);
			destination_struct = client_struct;

		}

		if (! socket_fd ) {
			socket_fd = socket(AF_INET, SOCK_DGRAM, 0);
		}
		
		sendto(
			socket_fd, 
			stream, 
			stream_length, 
			0,
			(struct sockaddr *)&destination_struct, 
			sizeof(destination_struct)
		);
		
		stream_length = 0;
		
	}

}

void f_data_indication(uint8_t *byte) {

	static void *buffer = NULL;
	static unsigned int counter = 0;
	static ssize_t received_size = -1;
	
	if (counter == 0) {
		
		if (buffer == NULL) {
			
			buffer = VOID(MAX_BUFFER_SIZE);
			
		}
		
		receive_data(buffer, &received_size);

		*byte = ((uint8_t *) buffer)[counter++];
	
	} else if (received_size == counter) {
		
		counter = 0;
	
		if (buffer != NULL) {
		
			free(buffer);
			buffer = NULL;
	
		}

		buffer = VOID(MAX_BUFFER_SIZE);
		
		receive_data(buffer, &received_size);
		*byte = ((uint8_t *) buffer)[counter++];

	} else {

		*byte = ((uint8_t *) buffer)[counter++];

	}

}

void receive_data(void *buffer, ssize_t *received_size) {
	
	struct sockaddr_in server_struct;
	socklen_t client_struct_size = sizeof(client_struct);

	if (! socket_fd ) {
		socket_fd = socket(AF_INET, SOCK_DGRAM, 0);
	}
	
	memset(&server_struct, 0, sizeof(server_struct));
	
	server_struct.sin_family = AF_INET;
	server_struct.sin_addr.s_addr = htonl(INADDR_ANY);
	server_struct.sin_port = htons(PORT);
	
	bind(socket_fd, (struct sockaddr *)&server_struct, sizeof(server_struct));
	
	*received_size = recvfrom(
		socket_fd, 
		buffer, 
		MAX_BUFFER_SIZE, 
		0, 
		(struct sockaddr *)&client_struct, 
		&client_struct_size
	);
	

}