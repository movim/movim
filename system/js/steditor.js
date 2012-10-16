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

        html += '<div class="menueditor">';
            html += '<a class="button tiny merged left" onclick="'+this.objectId+'.execCommand(\'formatblock\', \'<h1>\');"><img src="'+this.path+'images/h1.png" alt="H1" title="H1" ></a>';
            html += '<a class="button tiny merged" onclick="'+this.objectId+'.execCommand(\'formatblock\', \'<h2>\');"><img src="'+this.path+'images/h2.png" alt="H2" title="H2" ></a>';
            html += '<a class="button tiny merged" onclick="'+this.objectId+'.execCommand(\'formatblock\', \'<h3>\');"><img src="'+this.path+'images/h3.png" alt="H3" title="H3" ></a>';
            html += '<a class="button tiny merged right" onclick="'+this.objectId+'.execCommand(\'formatblock\', \'<h4>\');"><img src="'+this.path+'images/h4.png" alt="H4" title="H4" ></a>';
            
            html += '<a class="button tiny" style="margin-right: 5px;" onclick="'+this.objectId+'.execCommand(\'formatblock\', \'<p>\');"><img src="'+this.path+'images/paragraph.png" alt="Paragraph" title="Paragraph" ></a>';

            html += '<a class="button tiny merged left" onclick="'+this.objectId+'.execCommand(\'bold\')"><img src="'+this.path+'images/bold.png" alt="Bold" title="Bold"></a>';
            html += '<a class="button tiny merged" onclick="'+this.objectId+'.execCommand(\'italic\')"><img src="'+this.path+'images/italic.png" alt="Italic" title="Italic"></a>';
            html += '<a class="button tiny merged right" onclick="'+this.objectId+'.execCommand(\'underline\')"><img src="'+this.path+'images/underline.png" alt="Underline" title="Underline"></a>';

            html += '<a class="button tiny merged left" onclick="'+this.objectId+'.execCommand(\'justifyleft\')"><img src="'+this.path+'images/left.png" alt="Align Left" title="Align Left"></a>';
            html += '<a class="button tiny merged" onclick="'+this.objectId+'.execCommand(\'justifycenter\')"><img src="'+this.path+'images/center.png" alt="Center" title="Center"></a>';
            html += '<a class="button tiny merged right" onclick="'+this.objectId+'.execCommand(\'justifyright\')"><img src="'+this.path+'images/right.png" alt="Align Right" title="Align Right"></a>';

            html += '<a class="button tiny merged left" onclick="'+this.objectId+'.execCommand(\'insertorderedlist\')"><img src="'+this.path+'images/ol.png" alt="Ordered List" title="Ordered List"></a>';
            html += '<a class="button tiny merged right" onclick="'+this.objectId+'.execCommand(\'insertunorderedlist\')"><img src="'+this.path+'images/ul.png" alt="Unordered List" title="Unordered List"></a>';

            html += '<br /><a class="button tiny merged left" onclick="'+this.objectId+'.execCommand(\'outdent\')"><img src="'+this.path+'images/outdent.png" alt="Outdent" title="Outdent"></a>';
            html += '<a class="button tiny merged right" onclick="'+this.objectId+'.execCommand(\'indent\')"><img src="'+this.path+'images/indent.png" alt="Indent" title="Indent"></a>';

            html += '<a class="button tiny merged left" onclick="'+this.objectId+'.execCommand(\'createlink\')"><img src="'+this.path+'images/link.png" alt="Insert Link" title="Insert Link"></a>';
            html += '<a class="button tiny merged right" onclick="'+this.objectId+'.execCommand(\'insertimage\')"><img src="'+this.path+'images/picture.png" alt="Insert Image" title="Insert Image"></a>';

        html += '</div>';

        html += '<tr><td class="frame"><iframe style="height: 50px;" id="'+this.id+'-frame" frameborder="0"></iframe></td></tr>';

        html += '</table>';
        return html;
    };

    this.getFrameHtml = function() {
        var html = "";
        html += '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
        html += '<html><head>';
        html += '<meta http-equiv="Content-Type" content="text/html; charset='+this.charset+'">';
        html += '<title>SimpleTextEditor frame</title>';

        html += '<style type="text/css">html,body { cursor: text; } body { margin: 0px; padding: 0; font-size: 12px; }</style>';
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
    
    this.clearContent = function() {
        this.frame.document.body.innerHTML = '';
    };
}
