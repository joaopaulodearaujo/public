#ifndef __LINK__

#define __LINK__

#include <stdint.h>
#include "crc.h"

#define DELIMITER 0x7E
#define TIMEOUT 50000
#define BUFFER_SIZE 255

typedef struct {
	void *data;
	uint8_t size;
} lsdu_t;

typedef struct {
	uint8_t delimiter;
	uint8_t type;
	uint8_t length;
	uint8_t sequence; 
	uint32_t destination_address;
	uint32_t source_address;
	void *payload;
	crc_t crc;
}  frame_t;

typedef struct {
	uint8_t delimiter;
	uint8_t type;
	uint8_t sequence;
	uint32_t destination_address;
	uint32_t source_address;
} ack_t;

typedef struct {
	unsigned int sent;
	unsigned int received;
	unsigned int crc_error;
} statistics_t;


void reset_sequence();
void data_request(const char *, const lsdu_t *);
void data_indication(char *, char *, lsdu_t *);
void send_byte (const void *, uint8_t, bool_t, input_crc_t **, unsigned int *);

#endif 
