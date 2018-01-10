CC 		= gcc
CFLAGS 	= -O3 -I../gnuplot_i/src
RM		= rm -f
INCLUDES=-I/usr/include/postgresql
LIBS=-L/usr/lib -lpq -lm

all: sses_ps

key: sses_pswk

dbio.o: dbio.c
	$(CC) -c $(INCLUDES) dbio.c

sses_ps: plotsvys.o dbio.o ../gnuplot_i/gnuplot_i.o
	$(CC) -o sses_ps plotsvys.o dbio.o ../gnuplot_i/gnuplot_i.o $(LIBS); cp sses_ps ../web

sses_pswk: plotsvys.o dbio.o ../gnuplot_i/gnuplot_i.o
	$(CC) -o sses_ps plotsvys.o dbio.o ../../vendor/keylok/linux/keylok.o ../../vendor/keylok/linux/kfunc32.o ../gnuplot_i/gnuplot_i.o $(LIBS); cp sses_ps ../web

clean:
	rm -f sses_ps plotsvys.o dbio.o

