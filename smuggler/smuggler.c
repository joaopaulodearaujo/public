/************************************ 
 *
 *           Smuggler 
 *     An UDP-Based Reliable
 *   File Transfer Application 
 *        (fixed window)
 *
 * João Paulo de Araujo
 *
 *
 ***********************************/

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <pthread.h>
#include <stdint.h>
#include <getopt.h>
#include <errno.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <unistd.h>
#include <regex.h>
#include "smuggler.h"
#include "global.h"
#include "link.h"

FILE *debug_stream;
FILE *output_stream;
extern uint8_t error_probability;
extern statistics_t statistics;

int main(int argc, char **argv) {
	
	int character;
	char *filename = NULL;
	char *destination_address = NULL;
	char *destination_name = NULL;
	output_stream = stdout;
	pthread_t listener_thread;
	pthread_t sender_thread;
	app_t *output_struct;
	
	opterr = 0;
	error_probability = 0;
	debug_stream = fopen("/dev/null", "a");

	while ((character = getopt (argc, argv, "f:e:d:v:n:")) != -1) {
		
		switch (character) {

			/* file name */
			case 'f':
				filename = optarg;
			break;
			
			/* error probability */
			case 'e':
				error_probability = (uint8_t) atoi(optarg);
			break;
			
			/* destination_address */
			case 'd':
				destination_address = optarg;
			break;
			
			/* destination file name */
			case 'n':
				destination_name = optarg;
			break;
			
			/* debug output */
			case 'v':
				debug_stream = fopen(optarg, "w");
				
				if (debug_stream == NULL) {
					
					fprintf (
						stderr, 
						"Error trying to open/create file \"%s\" for writing: %s\n", 
						optarg, 
						strerror(errno)
					);
					
					exit (EXIT_FAILURE);

				}

			break;
			
			default:
				
				fprintf(output_stream, "Help: ./smuggler [-v output_log_file_name ] [-f source_file_name -n destination_file_name -d destination_address] [-e error_probability]\n");
				return EXIT_FAILURE;
	
		}

	}

	pthread_create(&listener_thread, NULL, listener, NULL);

	if (destination_address && filename && destination_name) {
		
		output_struct = MALLOC(1, app_t);

		output_struct -> destination_address = destination_address;
		output_struct -> destination_name = destination_name;
		output_struct -> filename = filename;

		pthread_create(&sender_thread, NULL, sender, (void *) output_struct);

	}

	pthread_exit(EXIT_SUCCESS);

}

void *listener() {
	
	int i = 0;
	int j = 0;
	size_t payload_length = 0;
	char destination_address[15];
	char source_address[15];
	char *filename = NULL;
	char *file_size_string = NULL;
	lsdu_t lsdu;
	regex_t regex;
	FILE *file_descriptor = NULL;
	
	if (regcomp(&regex, "^\\|[^|]+\\|[0-9]+\\|$", REG_EXTENDED|REG_NOSUB|REG_NEWLINE)) {
		
		fputs("Error validating input!\n", stderr);
		
		exit(EXIT_FAILURE);
	}
	
	for (;;) {
		
		data_indication(destination_address, source_address, &lsdu);
		
		if (lsdu.size > 0) {

			if (regexec(&regex, (char *) lsdu.data, 0, NULL, 0) != REG_NOMATCH) {

				i = 1;
				
				while (((char *) lsdu.data)[i] != APP_DELIMITER) {

					filename = (char *) realloc (filename, i);
					filename[i - 1] = ((char *) lsdu.data)[i];

					i++;
					
				}
				
				filename = (char *) realloc (filename, i);
				filename[i - 1] = '\0';
	
				file_descriptor = fopen(filename, "w+");
				
				if (file_descriptor == NULL) {
					
					fprintf (
						stderr, 
						"Error trying to open/create file \"%s\" for writing: %s\n", 
						filename, 
						strerror(errno)
					);
					
					exit (EXIT_FAILURE);

				}
	
				i++;
				
				j = 0;
				
				while (((char *) lsdu.data)[i] != APP_DELIMITER) {

					file_size_string = (char *) realloc (file_size_string, ++j);
					file_size_string[j - 1] = ((char *) lsdu.data)[i];
					
					i++;

				}
				
				file_size_string = (char *) realloc (file_size_string, ++j);
				file_size_string[j - 1] = '\0';
				
				fprintf(output_stream, "Receiving: %s (%s bytes)\n", filename, file_size_string);
	
			} else {
		
				payload_length += lsdu.size;
				
				progress_bar(atoi(file_size_string), lsdu.size);
				
				fwrite(lsdu.data, 1, lsdu.size, file_descriptor);
				
				fflush(file_descriptor);

			}
			
			free(lsdu.data);
			
			if (payload_length == atoi(file_size_string)) {
				
				reset_sequence();
			
				fclose(file_descriptor);
				
				payload_length = 0;
				
			}
			
		}

	}
	
	pthread_exit(NULL);

}

void *sender(void *data) {

	app_t *output_struct = (app_t *) data;
	size_t file_size;
	size_t read_size = 0;
	size_t remaining_size = 0;
	FILE *file_descriptor;
	void *output_data;
	struct stat file_stat;
	lsdu_t lsdu;

	file_descriptor = fopen(output_struct -> filename, "r");

	if (file_descriptor == NULL) {
		fprintf (stderr, "Error trying to read file \"%s\": %s\n", output_struct -> filename, strerror(errno));
		exit (EXIT_FAILURE);
	}
	
	stat(output_struct -> filename, &file_stat);
	
	file_size = (size_t) file_stat.st_size;
	
	lsdu.data = MALLOC(255, char);

	sprintf(lsdu.data, "%c%s%c%u%c", APP_DELIMITER, output_struct -> destination_name, APP_DELIMITER, file_size, APP_DELIMITER);
	
	lsdu.size = strlen(lsdu.data) + 1;
	
	data_request(output_struct -> destination_address, &lsdu);
	
	free(lsdu.data);
	
	output_data = VOID(BUFFER_SIZE);
	
	fprintf(output_stream, "Transmitting: %s (%i bytes)\n", output_struct -> filename, file_size);
	
	while (read_size != file_size) {
		
		remaining_size = file_size - read_size;
		
		fseek(file_descriptor, read_size, SEEK_SET);
	
		if (remaining_size < BUFFER_SIZE) {
			
			read_size += fread(output_data, 1, remaining_size, file_descriptor);
			
			lsdu.size = remaining_size;
		
		} else {

			read_size += fread(output_data, 1, BUFFER_SIZE, file_descriptor);
			
			lsdu.size = BUFFER_SIZE;
			
		
		}
	
		lsdu.data = output_data;

		fflush(output_stream);
		
		progress_bar(file_size, lsdu.size);
		
		data_request(output_struct -> destination_address, &lsdu);

	}
	
	reset_sequence();

	free(output_data);
	fclose(file_descriptor);
	
	
	pthread_exit(NULL);

} 

void progress_bar(size_t file_size, size_t read_size) {

	int bar_width = 70;
	int i;
	int position; 
	static float progress = 0.0;
	static int total_read_size = 0;

	fprintf(output_stream, "[");
	position = bar_width * progress;
	
	for (i = 0; i < bar_width; i++) {
		
		if (i < position) {
			fprintf(output_stream, "=");
		} else if (i == position) {
			fprintf(output_stream, ">");
		} else {
			fprintf(output_stream, " ");
		}
	}
	fprintf(output_stream, "] %i%% (%i bytes)", (int) (progress * 100), total_read_size);
	
	fflush(output_stream);
	
	progress += read_size / (float) file_size;
	total_read_size += read_size;

	fprintf(output_stream, "\r");
		
	if (total_read_size == file_size) {
		
		fprintf(output_stream, "[");
	
		for (i = 0; i < bar_width; i++) {
			
			if (i < bar_width - 1) {
				fprintf(output_stream, "=");
			} else {
				fprintf(output_stream, ">");
			} 
		}
		
		fprintf(output_stream, "] 100%% (%i bytes)\n\n", total_read_size);
		
		fprintf(output_stream, 
			"Sent Frames: %i\nReceived Frames: %i\nFrames with CRC error: %i\n",
			statistics.sent,
			statistics.received,
			statistics.crc_error
		);
		
		fprintf(output_stream, "--------------------------------------\n\n");		
		
		statistics.sent = 0;
		statistics.received = 0;
		statistics.crc_error = 0;

		progress = 0.0;
		total_read_size = 0;

	}
	
	fflush(output_stream);

}