;(function($) {
    
    $.fn.focusRange = function(start, end) {
        var $this = $(this);
        var domEl = $this.get(0);
        if (domEl.setSelectionRange) {
            domEl.focus();
            domEl.setSelectionRange(start, end);
        } else if (domEl.createTextRange) {
            var range = domEl.createTextRange();
            range.collapse(true);
            range.moveEnd('character', end);
            range.moveStart('character', start);
            range.select();
        }
        return $(this);
    },
    
    $.fn.focusAt = function(pos) {
        return $(this).focusRange(pos, pos);
    },
    
    $.fn.focusFirstInput = function() {
        return $(this).find('form :input:visible:first').focusRange(0, 0);
    },
    
    $.fn.isChildOf = function(selector){
        return this.parents(selector).length > 0;
    },
    
    $.fn.print_r = function(obj) {
        var count_obj = 0;
        function _output(str) {
            document.writeln(str + "<br/>");
        }
        function _print_r(obj, name, level) {
            var s = "";
            if (obj == undefined || level > 4) return;
            for (var i = 0; i < level; i++) { s += " | "; }
            s += " - " + name + ":" + typeof(obj) + "=" + obj;
            _output(s);
            if (name == "document" || typeof(obj) != "object") return;
            for ( key in obj ) {
            if (count_obj++ > 150) return;
                _print_r(obj[key], key, level + 1);
            }
        }
        _print_r(obj, "*", 0);
    };

    $.plugg = {
            
        init: function(options) {
            $.plugg.initFadeout();
            $.plugg.initCheckall();
            $.plugg.initCollapsible();
            $.plugg.initCSS3();
            $.plugg.initSortable();
        },
            
        initFadeout: function(options) {
            // Apply fadeout effect but cancel the effect when hovered
            $("#plugg .fadeout").fadeTo(100, 0.85)
                .animate({opacity:"+=0"}, 2000, function(){$("#plugg .fadeout").fadeOut("medium", function(){$(this).remove();});})
                .hover(function(){
                    if ($(this).is(':animated')) {
                        $(this).stop().fadeTo(100, 0.85);
                    }
                });
            
            // Fadeout flash messages when clicked
            $("#plugg-flash div")
                .fadeTo(100, 0.85)
                .click(function(){
                    $(this).unbind("hover").fadeOut("medium", function(){$(this).remove();});
                });
            
            // Place flash messages at the right top of the screen
            $("#plugg-flash").css({"position":"fixed", "top":"30px", "right":"20px", "width":"600px", "z-index":"5000"});
        },

        initCheckall: function(options) {
            // Highlight related table rows when a checkall checkbox is checked
            $("#plugg input.checkTrigger").show().click(function() {
                var $this = $(this);
                if ($this.attr("checked")) {
                    $this.closest("table").find("input.checkTarget").not(":disabled").attr("checked", "checked");
                } else {
                    $this.closest("table").find("input.checkTarget").removeAttr("checked");
                }
            });
        },

        initCollapsible: function(options) {
            // Collapse collapsible form elements
            $("#plugg div.plugg-collapsible, #plugg fieldset.plugg-collapsible").not(".plugg-collapsible-processed").each(function() {
                var $this = $(this);
                $this.find("span:first").wrap("<a class='plugg-collapsible' href=#></a>").end()
                    .find("a.plugg-collapsible:first").click(function() {
                        $(this).toggleClass("plugg-collapsed").closest(".plugg-collapsible-processed")
                            .toggleClass("plugg-collapsed").find(".plugg-form-field-content:first").slideToggle("fast");
                        return false;
                    }).end()
                    .addClass("plugg-collapsible-processed");
                if ($this.hasClass("plugg-collapsed")) {
                    $this.find(".plugg-form-field-content:first").css({"display":"none"}).end()
                        .find("a.plugg-collapsible:first").addClass("plugg-collapsed");
                }
            });
        },
        
        initCSS3: function(options) {
            // Add CSS3 compatible classes
            $(".plugg table, .plugg tbody").each(function() {
                $(this).children("tr:first-child").addClass("firstChild").end()
                    .children("tr:last-child").addClass("lastChild");
            });
            $(".plugg ul.plugg-list, .plugg ul.plugg-widget-entries, .plugg ul.plugg-menu, .plugg ul.plugg-pagination").each(function() {
                $(this).children("li:first-child").addClass("firstChild").end()
                    .children("li:last-child").addClass("lastChild");
            });
        },
        
        initSortable: function(options) {
            $(".plugg table.plugg-horizontal.plugg-sortable").not(".plugg-sortable-processed").find("tbody").sortable({
                axis: "y",
                cursor: "move",
                containment: "table.plugg-horizontal.plugg-sortable",
                handle: "a.draggableHandle",
                opacity: 0.6,
                helper: function(event, ui) {
                    ui.children().each(function() {
                        $(this).width($(this).width());
                    });
                    return ui;
                },
                stop: function(event, ui) {
                    jQuery(this).children("tr").removeClass("firstChild lastChild").end()
                        .children("tr:first-child").addClass("firstChild").end()
                        .children("tr:last-child").addClass("lastChild");
                }
            }).find("td:first-child").prepend("<a class='draggableHandle' href='#'>&nbsp;</a>").end()
                .closest("table").addClass("plugg-sortable-processed");
        },
    
        ajax: function(options) {
            var o = $.extend({
                type: 'get',
                url: '',
                data: '',
                processData: true,
                target: '#plugg-content',
                cache: true,
                onSuccess: '',
                onError: '',
                onContent: '',
                onSuccessUrl: '',
                onErrorUrl: '',
                onSuccessTarget: '',
                onErrorTarget: '',
                onSuccessRedirect: true,
                onErrorRedirect: true,
                effect: null,
                scrollTo: false,
                highlight: false,
                updateParent: false
            }, options);
            $.ajax({
                global: true,
                type: o.type,
                dataType: 'html',
                url: o.url,
                data: o.data,
                processData: o.processData,
                cache: o.cache,
                complete: function(xhr, textStatus) {                    
                    switch (textStatus) {
                    case 'success':
                        if (xhr.status == 278) {
                            // Plugg response was success
                            _handleSuccess(xhr);
                        } else {
                            // Plugg response was HTML content
                            _handleContent(xhr);
                        }
                        break;
                    case 'error':
                        _handleError(xhr);
                        break;
                    }
                }
            });
            
            function _handleSuccess(xhr) {
                try {
                    var result = JSON.parse(xhr.responseText);
                    if (o.onSuccess) o.onSuccess(xhr, result, $(o.target));
                    if (o.onSuccessUrl) {
                        $.plugg.ajax({
                            url: o.onSuccessUrl,
                            target: (o.onSuccessTarget == '') ? o.target : o.onSuccessTarget,
                            effect: o.effect,
                            highlight: true,
                            scrollTo: o.scrollTo,
                            cache: false
                        });
                    } else {
                        if (o.onSuccessRedirect) window.location = result.url;
                    }                        
                } catch (error) {
                    alert(error);
                    alert(xhr.responseText);
                }
            }
            
            function _handleContent(xhr) {
                var $target = o.updateParent ? $(o.target).parent() : $(o.target);
                
                // Effect
                switch (o.effect) {
                case 'slide':
                    $target.hide().html(xhr.responseText).slideDown('fast');
                    break;
                default:
                    $target.html(xhr.responseText).show();
                }
                
                if (o.highlight) $target.effect("highlight", {}, 1500);
                
                // Scroll to the updated content?
                if (o.scrollTo) {
                    $.scrollTo($target, 1000, {offset:{top:-10}});
                }

                if (o.onContent) o.onContent(xhr, $target);
            }
            
            function _handleError(xhr) {
                try {
                    var result = JSON.parse(xhr.responseText);
                    if (o.onError) o.onError(xhr, result, $(o.target));
                    if (o.onErrorUrl) {
                        $.plugg.ajax({
                            url: o.onErrorUrl,
                            target: (o.onErrorTarget == '') ? o.target : o.onErrorTarget,
                            effect: o.effect,
                            scrollTo: o.scrollTo,
                            cache: false
                        });
                    } else {
                        if (o.onErrorRedirect) window.location = result.url;
                    }
                } catch (error) {
                    alert(error);
                }
            }
        }
    }

})(jQuery);