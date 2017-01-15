CC 		= gcc
CFLAGS 	= -O3 -I../gnuplot_i/src
RM		= rm -f
INCLUDES=-I/usr/include/postgresql
LIBS=-L/usr/lib -lpq -lusb -lm

all: sses_gpd_rv

key: sses_gpdwk_rv

dbio.o: dbio.c
	$(CC) -c $(INCLUDES) dbio.c

sses_gpd_rv: plotdata.o dbio.o ../gnuplot_i/gnuplot_i.o
	$(CC) -o sses_gpd_rv plotdata.o dbio.o ../gnuplot_i/gnuplot_i.o $(LIBS); cp sses_gpd_rv /home/www-data/sses

sses_gpdwk_rv: plotdata.o dbio.o ../gnuplot_i/gnuplot_i.o
	$(CC) -o sses_gpd_rv plotdata.o dbio.o ../../vendor/keylok/linux/keylok.o ../../vendor/keylok/linux/kfunc32.o ../gnuplot_i/gnuplot_i.o $(LIBS); cp sses_gpd_rv /home/www-data/sses

dev: plotdata.o dbio.o ../gnuplot_i/gnuplot_i.o
	$(CC) -o sses_gpd_rv plotdata.o dbio.o ../gnuplot_i/gnuplot_i.o $(LIBS); cp sses_gpd_rv /sgta/www/sses
		
clean:
	rm -f sses_gpd plotdata.o dbio.o

