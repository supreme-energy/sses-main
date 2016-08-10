CC 		= gcc
CFLAGS 	= -O3 -I../gnuplot_i/src
RM		= rm -f
INCLUDES=-I/usr/include/postgresql
LIBS=-L/usr/lib -lpq -lusb -lm

all: sses_gva

key: sses_gvawk

dbio.o: dbio.c
	$(CC) -c $(INCLUDES) dbio.c

sses_gva: gva.o dbio.o
	$(CC) -o sses_gva gva.o dbio.o $(LIBS); cp sses_gva /home/www-data/sses

sses_gvawk: gva.o dbio.o
	$(CC) -o sses_gva gva.o dbio.o ../../vendor/keylok/linux/keylok.o ../../vendor/keylok/linux/kfunc32.o $(LIBS); cp sses_gva /home/www-data/sses

clean:
	rm -f sses_gva gva.o dbio.o

