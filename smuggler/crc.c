#include <stdint.h>
#include "crc.h"

#define WIDTH    (8 * sizeof(crc_t))
#define TOPBIT   (1 << (WIDTH - 1))

crc_t crcTable[256];

/*********************************************************************
 * 
 * Populate the partial CRC lookup table.
 *
 *
 *********************************************************************/
void crcInit(void){
	
	crc_t remainder;
	int dividend;
	unsigned char bit;
	
	/* Compute the remainder of each possible dividend. */
	for (dividend = 0; dividend < 256; ++dividend){
		
		/* Start with the dividend followed by zeros.*/
		remainder = dividend << (WIDTH - 8);
		
		/* Perform modulo-2 division, a bit at a time. */
		for (bit = 8; bit > 0; --bit){
			
			/* Try to divide the current data bit. */
			if (remainder & TOPBIT){
				
				remainder = (remainder << 1) ^ POLYNOMIAL;
				
			} else {
				
				remainder = (remainder << 1);
			}
		}
		
		/* Store the result into the table. */
		crcTable[dividend] = remainder;
	}
	
}

/*********************************************************************
 *
 * Compute the CRC of a given message. 
 * crcInit() must be called first.
 *
 * 
 *********************************************************************/
crc_t crcFast(const input_crc_t message[], int nBytes){
	
	crc_t remainder = INITIAL_REMAINDER;
	input_crc_t data;
	int byte;
	
	/* Divide the message by the polynomial, a byte at a time. */
	for (byte = 0; byte < nBytes; ++byte){
		
		data = (message[byte]) ^ (remainder >> (WIDTH - 8));
		remainder = crcTable[data] ^ (remainder << 8);
	}
	
	/* The final remainder is the CRC. */
	return (remainder);
	
}
