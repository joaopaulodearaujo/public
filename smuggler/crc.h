/**********************************************************************
 *
 * Filename:    crc.h
 * 
 * Description: A header file describing the various CRC standards.
 *
 * Notes:       
 *
 * 
 * Copyright (c) 2000 by Michael Barr.  This software is placed into
 * the public domain and may be used for any purpose.  However, this
 * notice must not be changed or removed and no warranty is either
 * expressed or implied by its publication or distribution.
 **********************************************************************/

#ifndef __CRC__
#define __CRC__

#define FALSE 0
#define TRUE !FALSE

#include <stdint.h>

/*
 * Select the CRC standard from the list that follows.
 */
#define CRC16

#if defined(CRC8)

typedef uint8_t crc_t;

#define CRC_NAME		"CRC-8"
#define POLYNOMIAL		0x29
#define INITIAL_REMAINDER	0xFF

#elif defined(CRC16)

typedef uint16_t crc_t;

#define CRC_NAME		"CRC-16"
#define POLYNOMIAL		0x8005
#define INITIAL_REMAINDER	0x0000

#endif

typedef uint8_t input_crc_t;

void crcInit();
crc_t crcFast(const input_crc_t *, int);

#endif 