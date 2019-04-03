up:
	cp ~/devel/pptext/pptext .
	cp ~/devel/pphtml/pphtml .
	rsync -av . rfrank@pgdp.org:public_html/test
