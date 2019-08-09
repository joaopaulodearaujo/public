CC=gcc
CFLAGS=-Wall -ansi -O3
EXEC=smuggler
OBJ=crc.o link.o physical.o

all: $(OBJ)
	$(CC) $(CFLAGS) -lpthread $(OBJ) smuggler.c -o $(EXEC)

crc.o: crc.c
	$(CC) $(CFLAGS) -c crc.c -o crc.o

link.o: link.c
	$(CC) $(CFLAGS) -D_BSD_SOURCE -c link.c -o link.o

physical.o: physical.c
	$(CC) $(CFLAGS) -c physical.c -o physical.o

clean:
	rm -f $(OBJ)

mrproper: clean
	rm -f $(EXEC)
