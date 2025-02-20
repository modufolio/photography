// Global vars
var currentPage = 1,
    entriesGallery = $("[data-fancybox='entries']"),
    entriesGalleryLength = $(entriesGallery).length,
    checkAjax = true;

$(document).ready(function() {
    disableGoogleAnalytics();
    notice();
    navigation();
    parallax();
});

// Offline Service Worker
if ("serviceWorker" in navigator) {
    $(window).on("load", function() {
        navigator.serviceWorker.register("/sw.js").then(function(registration) {
            // console.log('Service Worker registration was successful with scope: ', registration.scope);
        }, function(err) {
            console.log("ServiceWorker registration failed: ", err);
        });
    });
}

// Disable GA script
function disableGoogleAnalytics() {
    $(".gaOptOut").on("click", function () {
        gaOptout();
        alert("Google Analytics wurde deaktiviert!");
        return false;
    });
}

// Notice
function notice() {
    var body = $("body");
    var notice = $("#notice");

    // check cookie
    if ($(notice).length > 0) {
        if (Cookies.get("Notice")) {
            $(notice).remove();
        } else {
            $(notice).addClass("show");
            $(body).addClass("notice");
        }
    }

    // set cookie
    $(".agree").on("click", function(e) {
        $(notice).remove();
        $(body).removeClass("notice");
        Cookies.set("Notice", true, {
            path: "/",
            expire: 365
        });
        e.preventDefault();
    });
}

// Functions for the navigation
function navigation() {
    if ($("header").offset().top - $("main").offset().top === 0) {
        $(".navbar-default").addClass("on");
    } else {
        $(window).bind("scroll", function() {
            var navHeight = $(window).height() - 520;
            if ($(window).scrollTop() > navHeight) {
                $(".navbar-default").addClass("on");
            } else {
                $(".navbar-default").removeClass("on");
            }
        });
    }

    $("body").scrollspy({
        target: ".navbar-default",
        offset: 80
    });

    $("a.page-scroll").on("click", function() {
        if (location.pathname.replace(/^\//, "") === this.pathname.replace(/^\//, "") && location.hostname === this.hostname) {
            var target = $(this.hash);
            target = target.length ? target : $("[name=" + this.hash.slice(1) +"]");
            if (target.length) {
                smoothScroll(target);

                return false;
            }
        }
    });
}

// Parallax effect for the cover
function parallax() {
    var parallaxElements = $(".parallax");

    $(window).on("scroll", function () {
        window.requestAnimationFrame(function () {

            for (var i = 0; i < parallaxElements.length; i++) {
                var currentElement = parallaxElements.eq(i);
                var scrolled = $(window).scrollTop();

                currentElement.css({
                    "transform": "translate3d(0," + scrolled * -0.3 + "px, 0)"
                });
            }
        });
    });
}

// Lazy loads the entries
function lazyLoad() {
    $(window).on("scroll", function() {
        if (checkAjax && $(window).scrollTop() + $(window).height() > $(document).height() - 600) {
            checkAjax = false;
            $.when(loadEntries()).done(function() {
                checkAjax = true;
            });
        }
    });
}

// Ajax function to get the next entries
function loadEntries() {
    currentPage++;
    var paginateUrlPage = paginateUrl + "/" + currentPage,
        spinner = $("#spinner");

    $(spinner).show();

    return $.get(paginateUrlPage, function(data) {
        if (!data.length) {
            $(spinner).remove();
            checkAjax = false;
            return false;
        }

        $(data).insertBefore($(spinner));
        $(spinner).hide();
        $("[data-justified=\"true\"]").justifiedGallery("norewind");

        entriesGalleryLength = $("[data-fancybox='entries']").length;

        if ($.fancybox.getInstance()) { // If clicked throw lightbox
            addContentToLightbox(data);
        }
    });
}

// Callback because of asynchronous call
function addContentToLightbox(data) {
    $(data).each(function(index, element) {
        if (typeof $(element).data("src") !== "undefined") {
            $.fancybox.getInstance().addContent({
                type: "ajax",
                src: $(element).data("src")
            });
        }
    });

    $.fancybox.getInstance("updateControls", "force");
}

// Justify entries
function justify(rowHeight) {
    $("[data-justified=\"true\"]").justifiedGallery({
        rowHeight: rowHeight,
        maxRowHeight: "175%",
        lastRow: "nojustify",
        margins: 5,
        imagesAnimationDuration: 1000,
        captions: false
    });
}

// Lightbox (fancybox) for the image entries -> load dynamic content https://github.com/fancyapps/fancyBox/issues/257
function lightbox() {
    $.fancybox.defaults.lang = $("html").attr("lang");
    $.fancybox.defaults.hash = false;
    $.fancybox.defaults.autoFocus = false;
    $.fancybox.defaults.smallBtn = false;
    $.fancybox.defaults.buttons = ["close"];

    $.fancybox.defaults.beforeShow = function() {
        var entries = $("[data-fancybox='entries']");

        history.pushState(null, "", $(entries[this.index]).attr("href"));

        if (paginateUrl && checkAjax && this.index >= entriesGalleryLength - 3) {
            loadEntries();
        }
    };
    $.fancybox.defaults.afterClose = function() {
        history.pushState(null, "", pageUrl);
    };

    $(entriesGallery).fancybox();
}

// Loads next or prev entry for entry detail page links
function loadNextPrevEntry() {
    var spinner = "#spinner";
    $(spinner).hide();

    $("section#entry").on("click", "a.prev, a.next", function (e) {
        $(spinner).show();

        loadEntry($(this).attr("href"));

        e.preventDefault();
    }).on("swipeleft", function() {
        $(spinner).show();

        loadEntry($(this).find("a.prev").attr("href"));
    }).on("swiperight", function() {
        $(spinner).show();

        loadEntry($(this).find("a.next").attr("href"));
    });

    function loadEntry(url) {
        $.get(url, function(data) {
            var html = $.parseHTML(data);

            $("section#entry article").replaceWith($(html).find("section#entry article"));

            $("section#entry img").on("load", function() {
                $(spinner).hide();
            });

            history.pushState(null, "", url);
        });
    }
}

// Ajax replace for pagination
function pagination() {
    $("main").on("click", "ul.pagination a", function (e) {
        var url = $(this).attr("href"),
            pagination = $(this).closest("ul.pagination"),
            replace = $(pagination).data("replace");

        $.get(url, function(data) {
            var html = $.parseHTML(data);

            $(replace).replaceWith($(html).find(replace));

            smoothScroll(replace);

            history.pushState(null, "", url);
        });

        e.preventDefault();
    });
}

// Smooth scroll to an element
function smoothScroll(target) {
    $("html,body").animate({
        scrollTop: $(target).offset().top - 50
    }, 900);
}

// Create a new map with gpx track
function map(gpx, coordinates) {
    var map = L.map("map", {
        scrollWheelZoom: false
    }).setView(coordinates, 13);

    map.on("focus", function() { map.scrollWheelZoom.enable(); });
    map.on("blur", function() { map.scrollWheelZoom.disable(); });

    L.tileLayer("https://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png", {
        maxZoom: 18,
        attribution: "&copy; <a href=\"http://www.openstreetmap.org/copyright\">OpenStreetMap</a>"
    }).addTo(map);

    var track = omnivore.gpx(gpx);
    track.on("ready", function() {
        this.setStyle({
            color: "#00cdcd",
            weight: 5
        });
        this.eachLayer(function(marker) {
            marker.bindPopup(marker.feature.properties.name);
        });
    });

    map.addLayer(track);
}
