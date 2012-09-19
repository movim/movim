// +----------------------------------------------------------------+
// | SimpleTextEditor 1.0                                           |
// | Author: Cezary Tomczak [www.gosu.pl]                           |
// | Free for any use as long as all copyright messages are intact. |
// +----------------------------------------------------------------+

function SimpleTextEditor(id, objectId) {
    if (!id || !objectId) { alert("SimpleTextEditor.constructor(id, objectId) failed, two arguments are required"); }
    var self = this;
    this.id = id;
    this.objectId = objectId;
    this.frame;
    this.viewSource = false;
    
    this.path = "system/js/"; // with slash at the end
    this.cssFile = "";
    this.charset = "iso-8859-1";

    this.editorHtml = "";
    this.frameHtml = "";

    this.textareaValue = "";

    this.browser = {
        "ie": Boolean(document.body.currentStyle),
        "gecko" : (navigator.userAgent.toLowerCase().indexOf("gecko") != -1)
    };

    this.init = function() {
        if (document.getElementById && document.createElement && document.designMode && (this.browser.ie || this.browser.gecko)) {
            // EDITOR
            if (!document.getElementById(this.id)) { alert("SimpleTextEditor "+this.objectId+".init() failed, element '"+this.id+"' does not exist"); return; }
            this.textareaValue = document.getElementById(this.id).value;
            var ste = document.createElement("div");
            document.getElementById(this.id).parentNode.replaceChild(ste, document.getElementById(this.id));
            ste.id = this.id+"-ste";
            ste.innerHTML = this.editorHtml ? this.editorHtml : this.getEditorHtml();
            // BUTTONS
            var buttons = ste.getElementsByTagName("td");
            for (var i = 0; i < buttons.length; ++i) {
                if (buttons[i].className == "button") {
                    buttons[i].id = this.id+'-button-'+i;
                    //buttons[i].onmouseover = function() { this.className = "button"; }
                    buttons[i].onmouseout = function() { this.className = this.className.replace(/button-hover(\s)?/, "button"); }
                    buttons[i].onclick = function(id) { return function() { this.className = "button-hover button-click"; setTimeout(function(){ document.getElementById(id).className = document.getElementById(id).className.replace(/(\s)?button-click/, ""); }, 100); } }(buttons[i].id);
                }
            }
            // FRAME
            if (this.browser.ie) {
                this.frame = frames[this.id+"-frame"];
            } else if (this.browser.gecko) {
                this.frame = document.getElementById(this.id+"-frame").contentWindow;
            }
            this.frame.document.designMode = "on";
            this.frame.document.open();
            this.frame.document.write(this.frameHtml ? this.frameHtml : this.getFrameHtml());
            this.frame.document.close();
            insertHtmlFromTextarea();
        }
    };

    function lockUrls(s) {
        if (self.browser.gecko) { return s; }
        return s.replace(/href=["']([^"']*)["']/g, 'href="simpletexteditor://simpletexteditor/$1"');
    }

    function unlockUrls(s) {
        if (self.browser.gecko) { return s; }
        return s.replace(/href=["']simpletexteditor:\/\/simpletexteditor\/([^"']*)["']/g, 'href="$1"');
    }

    function insertHtmlFromTextarea() {
        try { self.frame.document.body.innerHTML = lockUrls(self.textareaValue); } catch (e) { setTimeout(insertHtmlFromTextarea, 10); }
    }

    this.getEditorHtml = function() {
        var html = "";
        html += '<input type="hidden" id="'+this.id+'" name="'+this.id+'" value="" autofocus="autofocus">';
        html += '<table class="ste" cellspacing="0" cellpadding="0">';
        //html += '<tr><td class="bar"><table id="'+this.id+'-buttons" cellspacing="0" cellpadding="0"><tr>';
        //html += '<td><div class="separator"></div></td>';
        html += '<div class="menueditor">';
            html += '<form><div class="element"><div class="select"><select onchange="'+this.objectId+'.execCommand(\'formatblock\', this.value);this.selectedIndex=0;"><option value=""></option><option value="<h1>">Heading 1</option><option value="<h2>">Heading 2</option><option value="<h3>">Heading 3</option><option value="<p>">Paragraph</option><option value="<pre>">Preformatted</option></select></div></div></form>';

            html += '<a class="button tiny merged left"><img src="'+this.path+'images/bold.gif" width="20" height="20" alt="Bold" title="Bold" onclick="'+this.objectId+'.execCommand(\'bold\')"></a>';
            html += '<a class="button tiny merged"><img src="'+this.path+'images/italic.gif" width="20" height="20" alt="Italic" title="Italic" onclick="'+this.objectId+'.execCommand(\'italic\')"></a>';
            html += '<a class="button tiny merged right"><img src="'+this.path+'images/underline.gif" width="20" height="20" alt="Underline" title="Underline" onclick="'+this.objectId+'.execCommand(\'underline\')"></a>';
            //html += '<td><div class="separator"></div></td>';
            html += '<a class="button tiny merged left"><img src="'+this.path+'images/left.gif" width="20" height="20" alt="Align Left" title="Align Left" onclick="'+this.objectId+'.execCommand(\'justifyleft\')"></a>';
            html += '<a class="button tiny merged"><img src="'+this.path+'images/center.gif" width="20" height="20" alt="Center" title="Center" onclick="'+this.objectId+'.execCommand(\'justifycenter\')"></a>';
            html += '<a class="button tiny merged right"><img src="'+this.path+'images/right.gif" width="20" height="20" alt="Align Right" title="Align Right" onclick="'+this.objectId+'.execCommand(\'justifyright\')"></a>';
            //html += '<td><div class="separator"></div></td>';
            html += '<a class="button tiny merged left"><img src="'+this.path+'images/ol.gif" width="20" height="20" alt="Ordered List" title="Ordered List" onclick="'+this.objectId+'.execCommand(\'insertorderedlist\')"></a>';
            html += '<a class="button tiny merged right"><img src="'+this.path+'images/ul.gif" width="20" height="20" alt="Unordered List" title="Unordered List" onclick="'+this.objectId+'.execCommand(\'insertunorderedlist\')"></a>';
            //html += '<td><div class="separator"></div></td>';
            html += '<a class="button tiny merged left"><img src="'+this.path+'images/outdent.gif" width="20" height="20" alt="Outdent" title="Outdent" onclick="'+this.objectId+'.execCommand(\'outdent\')"></a>';
            html += '<a class="button tiny merged right"><img src="'+this.path+'images/indent.gif" width="20" height="20" alt="Indent" title="Indent" onclick="'+this.objectId+'.execCommand(\'indent\')"></a>';
            //html += '<td><div class="separator"></div></td>';
            html += '<a class="button tiny merged left"><img src="'+this.path+'images/link.gif" width="20" height="20" alt="Insert Link" title="Insert Link" onclick="'+this.objectId+'.execCommand(\'createlink\')"></a>';
            html += '<a class="button tiny merged right"><img src="'+this.path+'images/image.gif" width="20" height="20" alt="Insert Image" title="Insert Image" onclick="'+this.objectId+'.execCommand(\'insertimage\')"></a>';

        html += '</div>';
        //html += '<td><div class="separator"></div></td>';
        //html += '<td class="button"><img src="'+this.path+'images/help.gif" width="20" height="20" alt="Help" title="Help" onclick="'+this.objectId+'.openWindow(\''+this.path+'help.html\', \'300\', \'300\')"></td>';
        //html += '</tr></table></td></tr>';
        html += '<tr><td class="frame"><iframe id="'+this.id+'-frame" frameborder="0"></iframe></td></tr>';
        //html += '<tr><td class="source"><input id="'+this.id+'-viewSource" type="checkbox" onclick="'+this.objectId+'.toggleSource()"> View Source</td></tr>';
        html += '</table>';
        return html;
    };

    this.getFrameHtml = function() {
        var html = "";
        html += '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
        html += '<html><head>';
        html += '<meta http-equiv="Content-Type" content="text/html; charset='+this.charset+'">';
        html += '<title>SimpleTextEditor frame</title>';
        //html += '<style type="text/css">pre { background-color: #eeeeee; padding: 0.75em 1.5em; border: 1px solid #dddddd; }</style>';
        //if (this.cssFile) { html += '<link rel="stylesheet" type="text/css" href="'+this.cssFile+'">'; }
        html += '<style type="text/css">html,body { cursor: text; } body { margin: 0px; padding: 0; }</style>';
        html += '</head><body></body></html>';
        return html;
    };

    this.openWindow = function(url, width, height) {
        var x = (screen.width/2-width/2);
        var y = (screen.height/2-height/2);
        window.open(url, "", "scrollbars=yes,width="+width+",height="+height+",screenX="+(x)+",screenY="+y+",left="+x+",top="+y);
    };

    this.toggleSource = function() {
        var html, text;
        if (this.browser.ie) {
            if (!this.viewSource) {
                html = this.frame.document.body.innerHTML;
                this.frame.document.body.innerText = unlockUrls(html);
                document.getElementById(this.id+"-buttons").style.visibility = "hidden";
                this.viewSource = true;
            } else {
                text = this.frame.document.body.innerText;
                this.frame.document.body.innerHTML = lockUrls(text);
                document.getElementById(this.id+"-buttons").style.visibility = "visible";
                this.viewSource = false;
            }
        } else if (this.browser.gecko) {
            if (!this.viewSource) {
                html = document.createTextNode(this.frame.document.body.innerHTML);
                this.frame.document.body.innerHTML = "";
                this.frame.document.body.appendChild(html);
                document.getElementById(this.id+"-buttons").style.visibility = "hidden";
                this.viewSource = true;
            } else {
                html = this.frame.document.body.ownerDocument.createRange();
                html.selectNodeContents(this.frame.document.body);
                this.frame.document.body.innerHTML = html.toString();
                document.getElementById(this.id+"-buttons").style.visibility = "visible";
                this.viewSource = false;
            }
        }
        document.getElementById(this.id+"-viewSource").checked = this.viewSource ? "checked" : "";
        document.getElementById(this.id+"-viewSource").blur();
    };

    this.execCommand = function(cmd, value) {
        if (cmd == "createlink" && !value) {
            var url = prompt("Enter URL:", "");
            if (url) {
                this.frame.focus();
                this.frame.document.execCommand("unlink", false, null);
                if (this.browser.ie) this.frame.document.execCommand(cmd, false, "simpletexteditor://simpletexteditor/"+url);
                else if (this.browser.gecko) this.frame.document.execCommand(cmd, false, url);
                this.frame.focus();
            }
        } else if (cmd == "insertimage" && !value) {
            var imageUrl = prompt("Enter Image URL:", "");
            if (imageUrl) {
                this.frame.focus();
                this.frame.document.execCommand(cmd, false, imageUrl);
                this.frame.focus();
            }
        } else {
            this.frame.focus();
            this.frame.document.execCommand(cmd, false, value);
            this.frame.focus();
        }
    };

    this.isOn = function() {
        return Boolean(this.frame);
    };

    this.getContent = function() {
        try { return unlockUrls(this.frame.document.body.innerHTML); } catch(e) { alert("SimpleTextEditor "+this.objectId+".getContent() failed"); }
    };

    this.submit = function() {
        if (this.isOn()) {
            if (this.viewSource) { this.toggleSource(); }
            document.getElementById(this.id).value = this.getContent();
        }
    };
}
