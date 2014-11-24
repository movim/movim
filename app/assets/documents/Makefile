HTMLER=texi2html
PDF=texi2pdf
DVI=texi2dvi
INFO=makeinfo
TEXI_OPT=-c -b
SRC=movim.texi
HTML_OUTP=bookhtml/
HTML_OPT=--no-menu --init-file texi2htmlrc --css-include=texi.css
HTML_OPT_DIR=--split=chapter --output=$(HTML_OUTP)
DOXYGEN=doxygen

.PHONY: all htmlmulti htmlmono dvi pdf reference

all: dvi pdf htmlmono htmlmulti reference info

clean:
	rm -rf bookhtml reference movim.dvi movim.pdf movim.info movim.html

htmlmulti: bookhtml/movim.html

htmlmono: movim.html

dvi: movim.dvi

pdf: movim.pdf

info: movim.info

reference: reference/html/index.html

movim.html: movim.texi fdl.texi
	$(HTMLER) $(HTML_OPT) $(SRC)

bookhtml/movim.html: movim.texi fdl.texi
	$(HTMLER) $(HTML_OPT) $(HTML_OPT_DIR) $(SRC)

movim.dvi: movim.texi fdl.texi
	$(DVI) $(TEXI_OPT) $(SRC)

movim.pdf: movim.texi fdl.texi
	$(PDF) $(TEXI_OPT) $(SRC)

movim.info: movim.texi fdl.texi
	$(INFO) $(SRC)

reference/html/index.html: Doxyfile
	$(DOXYGEN) Doxyfile
