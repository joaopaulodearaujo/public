#ifndef __GLOBAL__

#define __GLOBAL__

typedef enum {FALSE, TRUE} bool_t;

#define MALLOC(x, y) (y *) malloc (x * sizeof(y))
#define VOID(x) malloc(x)

#define DATA_FRAME 0x00
#define ACK_FRAME 0xFF

#endif 
