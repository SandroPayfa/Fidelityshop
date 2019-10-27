var GlobalLastSelect = false;
$(function() {
    var btn_add = $(".btn_select_layout");
    btn_add.on("click",function() {
        var typeLayout = $(this).attr('for');
        typeof typeLayout != "undefined" && gestionTemplate(typeLayout);
    });
    loadStyle();
    sendMail();
    $(".itm_g").click(function(e) {
        e.preventDefault();
        var f = $(this).attr("for");
        if (typeof f != 'undefined') {
            $(".itm_g").attr("class","itm_g");
            $(this).attr("class","itm_g item_on-g");
            $(".tablea-gestion").hide();
            $("#"+f).fadeIn();
        }
    });
    for (let w = 6; w < 72; w++) {
        $(".taill_element").append('<option value="'+w+'px">'+w+'</option>');
    }
    $(".style_action").change(function() {
        var n = $(this).attr("name"),v = $(this).val(),f = {};
        if (typeof n != "undefined" && typeof v != "undefined" && GlobalLastSelect) {
            f[n] = v;
            GlobalLastSelect.css(f);
        }
    });
    $(".style_action_img").change(function() {
        var n = $(this).attr("name"),v = $(this).val(),f = {};
        if (typeof n != "undefined" && typeof v != "undefined" && GlobalLastSelect) {
            f[n] = v+(n=="margin-top"||n=="margin-left"?"px":"");
            if (n=="margin-top") {
                f["margin-bottom"] = v+"px";
            }
            GlobalLastSelect.find("img").css(f);
            loadStyle();
        }
    });
    
    
    $(".style_action_line").change(function() {
        var n = $(this).attr("name"),v = $(this).val(),f = {},hr = GlobalLastSelect.find("hr");
        if (typeof n != "undefined" && typeof v != "undefined" && GlobalLastSelect && typeof  hr != "undefined" && hr.length >0) {
            f[n] = v;
            hr.css(f);
        }
    });

    $(".style_action_sup").click(function(e) {
        e.preventDefault();
        var f = $(this).attr("for");
        if (typeof f != "undefined"  && GlobalLastSelect) {
            var ol = GlobalLastSelect.clone(),g = ol.find(".select_effet-2").eq(0);
            switch (f) {
                case "up":
                   var el= GlobalLastSelect.prev();
                   if (el.length > 0) {
                        GlobalLastSelect.remove();
                        el.before(ol);
                        GlobalLastSelect = ol;
                        actionOnElement(GlobalLastSelect,$(".select_effet-2"));
                   }
                    break;
                case "down":
                    var el= GlobalLastSelect.next();
                    if (el.length > 0) {
                        GlobalLastSelect.remove();
                        el.after(ol);
                        GlobalLastSelect = ol;
                        actionOnElement(GlobalLastSelect,$(".select_effet-2"));
                    }
                    break;
                case "delete":
                    GlobalLastSelect.remove();
                    GlobalLastSelect = false;
                    $(".itm_g").eq(0).trigger("click");
                    break;
                case "bold": 
                    var b = GlobalLastSelect.css("font-weight");
                    b = (typeof b == "undefined"||b==""||b<600)?600:400;
                    GlobalLastSelect.css({fontWeight : b});
                    break;
                case "italic": 
                    var b = GlobalLastSelect.css("font-style");
                    b = (typeof b == "undefined"||b==""||b!="italic")?"italic":"normal";
                    GlobalLastSelect.css({fontStyle : b});
                    break;
            }
        }
    });
    $("#c_file_produit").change(function() {
        var v = $(this).val();
        if (typeof v != "undefined" && v.trim() != "") {
            $("#add_file").trigger("submit");
            wait("open",{text : "Chargement en cours..."})
        }
    });
    
    var bgColor = document.querySelectorAll('.bg-color');
    for (var i = 0; i < bgColor.length; i++) {
        var picker  = new CP(bgColor[i]);
        picker.on("change", function(color) {
            
            this.source.value = '#' + color;
            var n = this.source.getAttribute("name"),ff = this.source.getAttribute("for"),f = {};
            if (typeof n != "undefined" && color && GlobalLastSelect) {
                var hr = GlobalLastSelect.find("hr");
                if (typeof  hr != "undefined" && hr.length >0) {
                    f[n] = "#"+color;
                    hr.css(f);
                }
                else{
                    f[n] = "#"+color;
                    var el = (typeof ff != "undefined" && ff=="p")?$("#zoneTemplate"):GlobalLastSelect;
                    el.css(f);
                }
            }
           
        }).on("create",function() {
            var This = this, btnC =  document.createElement("div"),btn = document.createElement("button");
            
            btn.setAttribute("class","btn btn-primary");
            btn.setAttribute("style","font-size:10px");
            btn.innerText = "Fermer";

            btnC.setAttribute("class","d-block p-1 bg-dark text-right");
            
            btnC.appendChild(btn);

            this.self.appendChild(btnC);
            btn.addEventListener("click",function() {This.exit();});
        });
        
    }
    $("#sendToAllUser").click(function() {
        $("#destinataires-champ").attr("style", "");
        if ($(this).is(":checked")) {
            $("#listReceptor").val("");
            $("#destinataires-champ span").remove();
        }
    });
    $("#do_perce").click(function(e) {
        !e.preventDefault() && e.target;
        var w,msg = $("#editor-container div").eq(0),dst = $("#listReceptor").val(),obj = $("#objetBox").val(),sendToAllUser = false /*$("#sendToAllUser").is(":checked")*/,dat = {},ro = 0 ;
        
        $(window).scrollTop(0);
        
        
        if (empty(obj)) {$("#objetBox").attr("style", "border-color : #fb0000 !important").focus(function() {$(this).attr("style", "")});ro++}
        if (empty(dst) && !sendToAllUser) {$("#destinataires-champ").attr("style", "border-color : #fb0000 !important");ro++}
        else if (sendToAllUser) {dst = "all";}

        if (ro == 0) {
            w = wait("open",{text : "Envoi du message..."});
            dat = template();
            dat["dst"] = dst;
            dat["obj"] = obj;

            if(typeof dat["allImg"] == "undefined"){ dat["allImg"] = false;}
            
            request({link : "generate.php",data : dat, success : function(r) {
                if (r.end) {
                    w[0].html("<span class='text_option valid_operation'>Message envoyé</span>");
                    $("#listReceptor").val("");
                    $("#objetBox").val("");
                    setTimeout(function() {document.location.reload(true);},1500);
                }
                else{
                    w[0].html("<span class='text_option echec_operation'>Echec d'envoi</span>");
                    setTimeout(function() {wait("close");},1500);
                }
            }});
        }
    });
    function template() {
        var tm = $("#zoneTemplate"),Allimgs = tm.find("img"),imgS = {},html = tm.html(),bg = rgb2hex(tm.css("background-color"));
        for (var i = 0; i < Allimgs.length; i++) {
            imgS["image"+i] = Allimgs.eq(i).attr("src");
        }
        return {template : html,bg : bg,allImg : imgS};
    }
});
function gestionTemplate(l = false) {
    var all_l = {
        "one-h" : $("<h1 class='select_effet' style='display:block;padding:15px; background:#ffffff;font-size:20px;margin:0 10px;' contenteditable='true'>").html("Titre"),
        "one-p" : $("<p class='select_effet' style='display:block;padding:15px 15px; background:#ffffff;font-size:16px;text-align:left;margin:0 10px;' contenteditable='true'>").html("Paragraphe"),
        "one-table" : $("<table class='select_effet' style='display:block;padding:15px 15px; background:#ffffff;font-size:16px;text-align:left;margin:0 10px;'>").append($("<thead style='display: block;'>").append($("<tr style='width: 100%;display: block;'>").append($("<th style='font-weight:400;width: 49%;display: inline-block;'>").append($("<img src='image/image.jpg' style='display:block;margin:5px auto;width:100px;'/>"))).append($("<th style='font-weight:400;width: 49%;display: inline-block;' class='select_effet-2' contenteditable='true'>").html("Légende")))),
        "one-double-column" : $("<table  style='display:block;padding:15px 15px; background:#ffffff;font-size:16px;text-align:left;margin:0 10px;'>").append($("<thead style='display: block;'>").append($("<tr style='width: 100%;display: block;'>").append($("<th style='font-weight:400;width: 49%;display: inline-block;' class='select_effet-2' contenteditable='true'>").html("Paragraphe 1")).append($("<th class='select_effet-2' style='font-weight:400;width: 49%;display: inline-block;' contenteditable='true'>").html("Paragraphe 2")))),
        "one-l" : $("<div class='select_effet' style='display:block;padding:2px 5px; background:#ffffff;margin:0 10px;'>").append($("<hr style='margin:0;border:2px solid #000000'/>")),
        "one-image" : $("<div class='select_effet' style='display:block;padding:2px 5px; background:#ffffff;margin:0 10px;'>").append($("<img src='image/logo.png' style='display:block;margin:5px auto;width:100px;'/>")),
    },zoneTemplate = $("#zoneTemplate center").eq(0);
    if (l && typeof all_l[l] != "undefined") {
        
        if(GlobalLastSelect){
            GlobalLastSelect.after(all_l[l]);
        }
        else{
            zoneTemplate.append(all_l[l]);
        }
        
        GlobalLastSelect = all_l[l];
        actionOnElement(all_l[l],$(".select_effet-2"));
        
    }
}
function actionOnElement(el,el1) {
    el.on("click",function(e) {
           
        GlobalLastSelect = $(this);
        $(".select_effet").css({border : "initial"});
        $(this).css({border : "1px solid #ffa96f"});
        $(".itm_g").eq(1).trigger("click");
        loadStyle();

    }).on("mouseleave",function() {
        $(this).css({border : "initial"});
    });
    el1.on("focus",function(){
        var h = $(this).parent().parent().parent();
        h.css({border : "1px solid #ffa96f"});
        $(this).on("blur",function() {
            var h = $(this).parent().parent().parent();
            h.css({border : "initial"})
        });
        loadStyle();
    });
}
function loadStyle() {
    var papier = $("#zoneTemplate"),bg = papier.css("background-color"),bdBg = $(".style_action_papier"),n = $(".categori_style"),n1 = $(".categori_style_"),n2 = $(".categori_style__");
    if (GlobalLastSelect) {
        n.hide();
        n1.hide();
        n2.hide();
        var ll = GlobalLastSelect.find("hr"),img = GlobalLastSelect.find("img");
        if (typeof ll != "undefined" && ll.length != 0) {
            ll = ll.eq(0);
            var s = {
                input : {"border-color" : rgb2hex(ll.css("border-color"))},
                select : {"border-width" : ll.css("border-width")}
            };
            for(var key in s){
                for(var ke in s[key]){
                    var dat = s[key][ke];
                    if (key == "input") {
                        $("input[name='"+ke+"']").val(dat);
                    }
                    if (key == "select") {
                        var opt = $("select[name='"+ke+"']").find("option");
                        opt.prop("selected",false);
                        for (var fg = 0; fg < opt.length; fg++) {
                            var vv = opt.eq(fg).val();
                            if (vv.trim().toLowerCase() == dat.trim().toLowerCase()) {
                                opt.eq(fg).prop("selected",true);
                            }
                            
                        }
                    }
                }
            }
            n.eq(0).show();
            n1.show();
        }
        else{
            var t = true;
            if (typeof img != "undefined" && img.length > 0) {
                n2.show();
                var ma = Math,ims = {
                    input : {
                        width : img.css("width").replace("px",""),
                        height : img.css("height").replace("px",""),
                        "margin-top" : img.css("margin-top").replace("px",""),
                        "margin-left" : img.css("margin-left").replace("px","")
                    }
                }
                loadDetail(ims);
                t = GlobalLastSelect.children().length > 1?true:false;   
            }
            var s = {
                input : {
                    background : rgb2hex(GlobalLastSelect.css("background-color")),
                    color : rgb2hex(GlobalLastSelect.css("color"))
                },
                select : {
                    "font-family" : GlobalLastSelect.css("font-family"),
                    "font-size" : GlobalLastSelect.css("font-size"),
                    "text-align" : GlobalLastSelect.css("text-align")
                },
                btn : {
                    italic : GlobalLastSelect.css("font-style"),
                    bold : GlobalLastSelect.css("font-weight")
                }
            };
            loadDetail(s);
            n.show();
            !t && $("#text-block").hide();
        }
    }
    if (typeof bg != "undefined") {
        bg = rgb2hex(bg);
        bdBg.val(bg);
    }
    bdBg.change(function() {
        var v = $(this).val();
        if (typeof v != "undefined") {
            papier.css({background:v});
        }
    });
}
function loadDetail(s) {
    for(var key in s){
        for(var ke in s[key]){
            var dat = s[key][ke];
            if (key == "input") {
                $("input[name='"+ke+"']").val(dat);
            }
            if (key == "select") {
                var opt = $("select[name='"+ke+"']").find("option");
                opt.prop("selected",false);
                for (var fg = 0; fg < opt.length; fg++) {
                    var vv = opt.eq(fg).val();
                    if (vv.trim().toLowerCase() == dat.trim().toLowerCase()) {
                        opt.eq(fg).prop("selected",true);
                    }
                    
                }
            }
        }
    }
}
function rgb2hex(rgb){
    rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
    return (rgb && rgb.length === 4) ? "#" +
     ("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
     ("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
     ("0" + parseInt(rgb[3],10).toString(16)).slice(-2) : '';
}
function loadFile(p) {
    wait("close");
    $("#c_file_produit").val("");
    if (p.action) {
        GlobalLastSelect.find("img").attr("src","image/load/"+p.name);
    }
}
function wait(a,d) {
    var p1,p;
    if (typeof a != "undefined" && a == "open") {
        var L ,T,span = $("<span class='text_option'>");
        p1 = $("<div id='wait_operation' class='rounded'>");p = $("<div id='wait_operation_back'>");
        if (typeof d != "undefined" && typeof d.html != "undefined") {
            p1.html(d.html);
        }
        else{
            p1.append(span.text(typeof d.text != "undefined"?d.text:"..."));
        }
        $("body").append(p).append(p1).css({overflowY : "hidden"});
        L = ($(window).width() - p1.width())/2;T = ($(window).height() - p1.height())/2;
        p1.css({left : L+"px",top : T+"px"}).fadeIn();
        p.fadeIn();
    }
    else{
        p1 = $("#wait_operation"),p = $("#wait_operation_back")
        p1.fadeOut(function() {p.fadeOut(function() {
            p1.remove();
            p.remove();
            $("body").css({overflowY : "auto"});
        });});
    }
    return [p1,p];
}

function sendMail() {
    var destinataires = $("#destinataires-champ"),rsl = destinataires.find("div").eq(0),input = destinataires.find("input").eq(0);
    input.on("focus keyup",function(e) {
        var va = $(this).val(),co = e.keyCode,vldKey,ctn ;
        vldKey = (typeof co !="undefined" && (co == 13 || co == 32))?true:false;
        destinataires.attr("style","");
        
        if (!empty(va)) {
            if (typeof co =="undefined" || co != 13) {rsl.show();}
            ctn = (vldKey && contentType(va,"email"))?true:false;
            if (vldKey) {
                var alE = va.split(" ");
                
                for (let x = 0; x < alE.length; x++) {
                    if ( contentType(alE[x],"email") ) {
                        addToListEmail(alE[x].trim());
                    }
                }
                $(this).val("").focus();
            }
            
        }
    }).keydown(function(e) {
        if(e.keyCode == 13){!e.preventDefault() && e.target;}
    }).on("blur",function() {
        var va = $(this).val();
        
        if (!empty(va)) {
            var alE = va.split(" "),em = false;
            for (let x = 0; x < alE.length; x++) {
                if ( contentType(alE[x],"email") ) {
                    addToListEmail(alE[x].trim());
                    em = true;
                }
            }
            if(em){
                $(this).val("").focus();
            }
        }
    });
    prelisteEmail();
    addToListEmail($("#listReceptor").val(),true);
    function addToListEmail(v = "",ss = false) {
        var list = $("#listReceptor").val(),nv,inp = $("#destinataires-champ input").eq(0),lst;
        if (!empty(v)) {
            nv = v.trim().split(";");
            for (var k = 0; k < nv.length; k++) {
                var dlt = $("<a class='btn bg-danger text-white p-1 m-0 ml-1'>").text("x");
                if (typeof nv[k] !="undefined"  && (!inArray(nv[k],list.split(";")) || ss)) {
                    if (!ss) {list += (empty(list)?"":";")+nv[k];}
                    inp.before($("<span class='p-1 d-inline-block border bg-light ml-1'>").append($("<i>").text(nv[k])).append(dlt));
                    dlt.click(function(e) {
                        !e.preventDefault() && e.target;
                        var p = $(this).parent(),ls = "",gh = $("#listReceptor").val().trim().split(";");
                        for (var x = 0; x < gh.length; x++) {
                            if (gh[x].trim() != p.find("i").eq(0).text().trim()) {
                                ls += (empty(ls)?"":";")+gh[x];
                            }
                        }
                        p.remove();
                        $("#listReceptor").val(ls);
                        
                        if(empty($("#listReceptor").val().replace(";",""))){$("#info-dst").show()}
                        else{$("#info-dst").hide();}
                    });
                }
                
            }
            $("#sendToAllUser").prop("checked",false);
        }
        if (typeof $("#listReceptor").val() != "undefined") {
            $("#listReceptor").val(list);
            if(empty($("#listReceptor").val().replace(";",""))){$("#info-dst").show()}
            else{$("#info-dst").hide();}
        }
        
    }
    function prelisteEmail() {
        var lst = $("#preliste"),lstData = lst.attr("data"),dsp = $("#listeEmail"),allSelectNumber = $("#allSelectNumber"),allEml = $("#allEml");
        if (typeof lstData != "undefined") {
            lstData = lstData.split(";");
            var t = 0,liste,select_all,action_commune = $("#add_email_from_pre");
            dsp.html("");
            
            for (var k = 0; k < lstData.length; k++) {
                t++;
                var email = lstData[k].trim(),line = $('<div class="everyEmail pt-2 pb-2 m-auto border-bottom">').append($('<div class="custom-control custom-checkbox ml-2">').append($('<input type="checkbox" value="'+email+'" class="custom-control-input bg-white" id="selectEmail'+t+'">')).append($('<label class="custom-control-label" for="selectEmail'+t+'">').text(email)));
                if (email != "") {
                    dsp.append(line);
                }
            }
            liste = $(".everyEmail input");
            select_all = $("#selectAllEmails");
    
            action_commune.prop("disabled",true);
            liste.prop("checked",false);
            select_all.prop("checked",false).click(function() {
                var all = $(this).is(":checked")?true:false;
               for (var j = 0; j < liste.length; j++) {
                   var element = liste.eq(j);
                   if ((element.is(":checked") && !all) || (!element.is(":checked") && all)) {element.trigger("click");}
               }
               allSelectNumber.html(liste.length+" Sélèction(s)");
            });
            liste.click(function() {
                var all = 0,t = liste.length;for (var j = 0; j < t; j++) {var element = liste.eq(j);if (element.is(":checked")) {all++;}}
                if (all == t && !select_all.is(":checked" )) {select_all.prop("checked",true);}
                else if (all != t && select_all.is(":checked")) {select_all.prop("checked",false)}
                
                action_commune.attr("disabled",true);
                if (all != 0) {action_commune.attr("disabled",false);}
                allSelectNumber.html(all+" Sélèction(s)");
            });
            action_commune.click(function() {
                for (var i = 0; i < liste.length; i++) {
                    var li=liste.eq(i);vl = li.val();
                    if (li.is(":checked")) {
                        if (typeof vl != "undefined") {
                            addToListEmail(vl.trim());
                        }
                    }
                }
                select_all.prop("checked",false);
                liste.prop("checked",false);
                action_commune.prop("disabled",true);
                allSelectNumber.html("0 Sélèction(s)");
            })
        }
    }
}
