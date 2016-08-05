CC 		= gcc
CFLAGS 	= -O3 -I../gnuplot_i/src
RM		= rm -f
INCLUDES=-I/usr/include/postgresql
LIBS=-L/usr/lib -lpq -lusb -lm

all: sses_dsp

dbio.o: dbio.c
	$(CC) -c $(INCLUDES) dbio.c

sses_dsp: plotdata.o dbio.o ../gnuplot_i/gnuplot_i.o
	$(CC) -o sses_dsp plotdata.o dbio.o ../../vendor/keylok/linux/keylok.o ../../vendor/keylok/linux/kfunc32.o ../gnuplot_i/gnuplot_i.o $(LIBS); cp sses_dsp /home/www-data/sses

clean:
	rm -f sses_dsp plotdata.o dbio.o

