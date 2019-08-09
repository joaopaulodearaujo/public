#ifndef __APPLICATION__

#define __APPLICATION__

typedef struct {
	char *destination_address;
	char *destination_name;
	char *filename;
} app_t;

#define APP_DELIMITER '|'

void *sender(void *);
void *listener();
void read_file(const char *, void **, size_t *);
void progress_bar(size_t, size_t);


#endif