/*
 * Rijksvideo. 
 *
 * Plugin Name:         Rijksvideo
 * Plugin URI:          https://wbvb.nl/plugins/rhswp-rijksvideo/
 * Description:         De mogelijkheid om video's in te voegen met diverse media-formats en ondertitels
 * Version:             1.0.1
 * Version description: Accessibility checks
 * Author:              Paul van Buuren
 * Author URI:          https://wbvb.nl
 * License:             GPL-2.0+
 */


jQuery(function ($) {
    $.fn.createPlayer = function() {
        var t = {
            txt: []
        }, e = $.extend({}, t);
        return this.each(function() {
            function t() {
                $this.settings.txt.play = videos.attr("data-playtxt") || "Afspelen",
                    $this.settings.txt.pauze = videos.attr("data-pauzetxt") || "Pauzeer",
                    $this.settings.txt.enableAd = videos.attr("data-enablead") || "Audio descriptie afspelen",
                    $this.settings.txt.disableAd = videos.attr("data-disablead") || "Audio descriptie stoppen",
                    $this.settings.txt.enableCc = videos.attr("data-enablecc") || "Ondertiteling aan",
                    $this.settings.txt.disableCc = videos.attr("data-disablecc") || "Ondertiteling uit",
                    $this.settings.txt.volumeOn = videos.attr("data-enablevolume") || "Geluid aan",
                    $this.settings.txt.volumeOff = videos.attr("data-disablevolume") || "Geluid uit",
                    $this.settings.txt.enableFullscreen = videos.attr("data-enablefullscreen") || "Schermvullende weergave openen",
                    $this.settings.txt.disableFullscreen = videos.attr("data-disablefullscreen") || "Schermvullende weergave sluiten"
            }
            function init(t) {
              
                $this.settings.playButton           = $this.find(".mejs-playpause-button button"),
                    $this.settings.adButton         = $this.find(".mejs-audiodescription-button button"),
                    $this.settings.subButton        = $this.find(".mejs-captions-button button"),
                    $this.settings.volButton        = $this.find(".mejs-volume-button button"),
                    $this.settings.fullscreenButton = $this.find(".mejs-fullscreen-button button"),
                    togglePlayState(),
                    toggleAudioDescription(),
                    toggleSubtitles(),
                    toggleVolume(),
                    toggleFullscreen(),
                    t.addEventListener("play", function() {
                        togglePlayState()
                    }, !1),
                    t.addEventListener("playing", function() {
                        togglePlayState()
                    }, !1),
                    t.addEventListener("pause", function() {
                        togglePlayState()
                    }, !1),
                    $this.settings.adButton.on("click", function() {
                        toggleAudioDescription()
                    }),
                    $this.settings.subButton.on("click", function() {
                        toggleSubtitles()
                    }),
                    $this.settings.volButton.on("click", function() {
                        toggleVolume()
                    }),
                    $this.settings.fullscreenButton.on("click", function() {
                        toggleFullscreen()
                    })
            }
            function togglePlayState() {
                $this.settings.playButton.parent().hasClass("mejs-play") ? ($this.settings.playButton.attr({
                    title: $this.settings.txt.play,
                    "aria-label": $this.settings.txt.play
                }),
                    $this.settings.playButton.html($this.settings.txt.play)) : ($this.settings.playButton.attr({
                    title: $this.settings.txt.pauze,
                    "aria-label": $this.settings.txt.pauze
                }),
                    $this.settings.playButton.html($this.settings.txt.pauze))
            }
            function toggleAudioDescription() {
                $this.settings.adButton.hasClass("inactive") ? ($this.settings.adButton.removeClass("inactive"),
                    $this.settings.adButton.attr({
                        title: $this.settings.txt.disableAd,
                        "aria-label": $this.settings.txt.disableAd
                    }),
                    $this.settings.adButton.html($this.settings.txt.disableAd)) : ($this.settings.adButton.addClass("inactive"),
                    $this.settings.adButton.attr({
                        title: $this.settings.txt.enableAd,
                        "aria-label": $this.settings.txt.enableAd
                    }),
                    $this.settings.adButton.html($this.settings.txt.enableAd))
            }
            function toggleSubtitles() {
                $this.settings.subButton.hasClass("inactive") ? ($this.settings.subButton.removeClass("inactive"),
                    $this.settings.subButton.attr({
                        title: $this.settings.txt.disableCc,
                        "aria-label": $this.settings.txt.disableCc
                    }),
                    $this.settings.subButton.html($this.settings.txt.disableCc)) : ($this.settings.subButton.addClass("inactive"),
                    $this.settings.subButton.attr({
                        title: $this.settings.txt.enableCc,
                        "aria-label": $this.settings.txt.enableCc
                    }),
                    $this.settings.subButton.html($this.settings.txt.enableCc))
            }
            function toggleVolume() {
                $this.settings.volButton.hasClass("inactive") ? ($this.settings.volButton.removeClass("inactive"),
                    $this.settings.volButton.attr({
                        title: $this.settings.txt.volumeOn,
                        "aria-label": $this.settings.txt.volumeOn
                    }),
                    $this.settings.volButton.html($this.settings.txt.volumeOn)) : ($this.settings.volButton.addClass("inactive"),
                    $this.settings.volButton.attr({
                        title: $this.settings.txt.volumeOff,
                        "aria-label": $this.settings.txt.volumeOff
                    }),
                    $this.settings.volButton.html($this.settings.txt.volumeOff))
            }
            function toggleFullscreen() {
                $this.settings.fullscreenButton.hasClass("fullscreen") ? ($this.settings.fullscreenButton.attr({
                    title: $this.settings.txt.disableFullscreen,
                    "aria-label": $this.settings.txt.disableFullscreen
                }),
                    $this.settings.fullscreenButton.html($this.settings.txt.disableFullscreen),
                    $this.settings.fullscreenButton.removeClass("fullscreen")) : ($this.settings.fullscreenButton.attr({
                    title: $this.settings.txt.enableFullscreen,
                    "aria-label": $this.settings.txt.enableFullscreen
                }),
                    $this.settings.fullscreenButton.html($this.settings.txt.enableFullscreen),
                    $this.settings.fullscreenButton.addClass("fullscreen"))
            }
            function toggleDownloads() {
              var uniquecounter = 0;
                $this.find(".toggle").each(function() {
                  uniquecounter++;
                    $(this).addClass("close");
                    var t = $(this).find("h2")
                        , e = t.text();
                }),
                    $this.find(".toggle h2 a").click(function(t) {
                        t.preventDefault(),
                          $(this).parents("li").toggleClass("close").toggleClass("open"),
                          $(this).attr('aria-expanded', ( $(this).attr('aria-expanded') =='false' ? 'true' : 'false' ) )
                    })
            }
            function d() {
                $this.find("embed").length > 0 && $this.addClass("flash")
            }
            function createVideoPlayers() {
                var t = -1
                    , e = -1;
                videos.mediaelementplayer({
                    enableAutosize: true,
                    videoWidth: t,
                    videoHeight: e,
                    mode: "auto",
                    plugins: ["flash"],
                    pluginPath: "/wp-content/themes/wp-rijkshuisstijl/assets/",
                    flashName: "flashmediaelement.swf",
                    features: ["playpause", "current", "progress", "duration", "volume", "tracks", "audiodescription", "fullscreen"],
                    adFile: videos.data("ad"),
                    alwaysShowControls: true,
                    toggleCaptionsButtonWhenOnlyOne: true,
                    success: function(t) {
                        init(t),
                            d()
                    }
                })
            }
            function createStreamingVideoPlayers() {
                var t = -1
                    , e = -1;
                videos.mediaelementplayer({
                    enableAutosize: true,
                    videoWidth: t,
                    videoHeight: e,
                    mode: "auto",
                    plugins: ["flash"],
                    pluginPath: "/wp-content/themes/wp-rijkshuisstijl/assets/",
                    flashName: "flashmediaelement.swf",
                    features: ["playpause", "volume", "fullscreen"],
                    type: "application/x-mpegURL",
                    alwaysShowControls: true,
                    success: function(t) {
                        init(t),
                            d()
                    },
                    error: function() {
                        $this.html(videos.attr("data-noplugintxt"))
                    }
                })
            }
            function createAudioPlayers() {
                audios.mediaelementplayer({
                    enableAutosize: true,
                    mode: "auto",
                    plugins: ["flash"],
                    pluginPath: "/wp-content/themes/wp-rijkshuisstijl/assets/",
                    flashName: "flashmediaelement.swf",
                    features: ["playpause", "current", "progress", "duration", "volume"],
                    alwaysShowControls: true,
                    success: function(t) {
                        init(t)
                    }
                })
            }
            function createPlayers() {
                $this.settings = e,
                    t(),
                    videos.length > 0 ? $this.hasClass("streaming") ? createStreamingVideoPlayers() : (createVideoPlayers(),
                        toggleDownloads()) : audios.length > 0 && (createAudioPlayers(),
                        toggleDownloads())
            }
            var $this = $(this)
                , videos = $this.find("video")
                , audios = $this.find("audio");
            createPlayers()
        })
    };
    $(document).ready(function() {
        $(".block-audio-video video").css('width', '100%').css('height', '100%');
        $(".block-audio-video").createPlayer();
    })
});

jQuery('audio,video').mediaelementplayer({
	//mode: 'shim',
	success: function(player, node) {
		jQuery('#' + node.id + '-mode').html('mode: ' + player.pluginType);
	}
});


