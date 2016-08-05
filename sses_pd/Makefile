CC 		= gcc
CFLAGS 	= -O3 -I../gnuplot_i/src
RM		= rm -f
INCLUDES=-I/usr/include/postgresql
LIBS=-L/usr/lib -lpq -lusb -lm

all: sses_pd

key: sses_pdwk

dbio.o: dbio.c
	$(CC) -c $(INCLUDES) dbio.c

sses_pd: plotdata.o dbio.o ../gnuplot_i/gnuplot_i.o
	$(CC) -o sses_pd plotdata.o dbio.o ../gnuplot_i/gnuplot_i.o $(LIBS); cp sses_pd /home/www-data/sses

sses_pdwk: plotdata.o dbio.o ../gnuplot_i/gnuplot_i.o
	$(CC) -o sses_pd plotdata.o dbio.o ../../vendor/keylok/linux/keylok.o ../../vendor/keylok/linux/kfunc32.o ../gnuplot_i/gnuplot_i.o $(LIBS); cp sses_pd /home/www-data/sses

clean:
	rm -f sses_pd plotdata.o dbio.o

