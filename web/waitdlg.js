var ray={
ajax:function(st) { this.show('load'); },
show:function(el) { this.getID(el).style.display=''; },
getID:function(el) { return document.getElementById(el); }
}
var rayreload={
ajax:function(st) { this.show('reload'); },
show:function(el) { this.getID(el).style.display=''; },
getID:function(el) { return document.getElementById(el); }
}
