#ifndef __PHYSICAL__

#define __PHYSICAL__

#include <stdint.h>

#define DATA_STREAM_BASE_SIZE 14
#define ACK_STREAM_BASE_SIZE 11
#define MAX_BUFFER_SIZE DATA_STREAM_BASE_SIZE + 255
#define PORT 4293
#define ERROR_MASK 0x42

void f_data_request(uint8_t);
void f_data_indication(uint8_t *);
void receive_data(void *, ssize_t *);

#endif