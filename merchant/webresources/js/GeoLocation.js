(function() {
    if (window.google && google.gears) {
        return;
    }
    var factory = null;
    if (typeof(GearsFactory) != 'undefined') {
        factory = new GearsFactory();
    } else {
        try {
            factory = new ActiveXObject('Gears.Factory');
            if (factory.getBuildInfo().indexOf('ie_mobile') != -1) {
                factory.privateSetGlobalObject(this);
            }
        } catch (e) {
            if ((typeof(navigator.mimeTypes) != 'undefined') && navigator.mimeTypes["application/x-googlegears"]) {
                factory = document.createElement("object");
                factory.style.display = "none";
                factory.width = 0;
                factory.height = 0;
                factory.type = "application/x-googlegears";
                document.documentElement.appendChild(factory);
                if (factory && (typeof(factory.create) == 'undefined')) {
                    factory = null;
                }
            }
        }
    } if (!factory) {
        return;
    }
    if (!window.google) {
        google = {};
    }
    if (!google.gears) {
        google.gears = {
            factory: factory
        };
    }
})();
var bb_success;
var bb_error;
var bb_webTimeout_id = -1;

function handleLocationTimeout() {
    if (bb_webTimeout_id != -1) {
        bb_error({
            message: "Timeout error",
            code: 3
        })
    }
}

function handleLocation() {
    clearTimeout(bb_webTimeout_id);
    bb_webTimeout_id = -1;
    if (bb_success && bb_error) {
        if (web.location.latitude == 0 && web.location.longitude == 0) {
            bb_error({
                message: "Position unavailable",
                code: 2
            })
        } else {
            var a = null;
            if (web.location.timestamp) {
                a = new Date(web.location.timestamp)
            }
            bb_success({
                timestamp: a,
                coords: {
                    latitude: web.location.latitude,
                    longitude: web.location.longitude
                }
            })
        }
        bb_success = null;
        bb_error = null
    }
}
var geo_position_js = function() {
	
    var b = {};
    var c = null;
    var a = "undefined";
    b.getCurrentPosition = function(f, d, e) {
        c.getCurrentPosition(f, d, e)
    };
    b.init = function() {
        try {
            if (typeof(geo_position_js_simulator) != a) {
                c = geo_position_js_simulator
            } else {

                if (typeof(bondi) != a && typeof(bondi.geolocation) != a) {
                    c = bondi.geolocation
                } else {
                    if (typeof(navigator.geolocation) != a) {
                        c = navigator.geolocation;
                        b.getCurrentPosition = function(h, e, g) {
                            function f(i) {
                                if (typeof(i.latitude) != a) {
                                    h({
                                        timestamp: i.timestamp,
                                        coords: {
                                            latitude: i.latitude,
                                            longitude: i.longitude
                                        }
                                    })
                                } else {
                                    h(i)
                                }
                            }
                            c.getCurrentPosition(f, e, g)
                        }
                    } else {
                        if (typeof(window.web) != a && web.location.GPSSupported) {
                            if (typeof(web.location.setAidMode) == a) {
                                return false
                            }
                            web.location.setAidMode(2);
                            b.getCurrentPosition = function(g, e, f) {
                                bb_success = g;
                                bb_error = e;
                                if (f.timeout) {
                                    bb_webTimeout_id = setTimeout("handleLocationTimeout()", f.timeout)
                                } else {
                                    bb_webTimeout_id = setTimeout("handleLocationTimeout()", 60000)
                                }
                                web.location.onLocationUpdate("handleLocation()");
                                web.location.refreshLocation()
                            };
                            c = web.location
                        } else {
                            if (typeof(window.google) != a && typeof(google.gears) != a) {
                                c = google.gears.factory.create("beta.geolocation")
                            } else {
                                if (typeof(Mojo) != a && typeof(Mojo.Service.Request) != "Mojo.Service.Request") {
                                    c = true;
                                    b.getCurrentPosition = function(g, e, f) {
                                        parameters = {};
                                        if (f) {
                                            if (f.enableHighAccuracy && f.enableHighAccuracy == true) {
                                                parameters.accuracy = 1
                                            }
                                            if (f.maximumAge) {
                                                parameters.maximumAge = f.maximumAge
                                            }
                                            if (f.responseTime) {
                                                if (f.responseTime < 5) {
                                                    parameters.responseTime = 1
                                                } else {
                                                    if (f.responseTime < 20) {
                                                        parameters.responseTime = 2
                                                    } else {
                                                        parameters.timeout = 3
                                                    }
                                                }
                                            }
                                        }
                                        r = new Mojo.Service.Request("palm://com.palm.location", {
                                            method: "getCurrentPosition",
                                            parameters: parameters,
                                            onSuccess: function(h) {
                                                g({
                                                    timestamp: h.timestamp,
                                                    coords: {
                                                        latitude: h.latitude,
                                                        longitude: h.longitude,
                                                        heading: h.heading
                                                    }
                                                })
                                            },
                                            onFailure: function(h) {
                                                if (h.errorCode == 1) {
                                                    e({
                                                        code: 3,
                                                        message: "Timeout"
                                                    })
                                                } else {
                                                    if (h.errorCode == 2) {
                                                        e({
                                                            code: 2,
                                                            message: "Position Unavailable"
                                                        })
                                                    } else {
                                                        e({
                                                            code: 0,
                                                            message: "Unknown Error: webOS-code" + errorCode
                                                        })
                                                    }
                                                }
                                            }
                                        })
                                    }
                                } else {
                                    if (typeof(device) != a && typeof(device.getServiceObject) != a) {
                                        c = device.getServiceObject("Service.Location", "ILocation");
                                        b.getCurrentPosition = function(g, e, f) {
                                            function i(l, k, j) {
                                                if (k == 4) {
                                                    e({
                                                        message: "Position unavailable",
                                                        code: 2
                                                    })
                                                } else {
                                                    g({
                                                        timestamp: null,
                                                        coords: {
                                                            latitude: j.ReturnValue.Latitude,
                                                            longitude: j.ReturnValue.Longitude,
                                                            altitude: j.ReturnValue.Altitude,
                                                            heading: j.ReturnValue.Heading
                                                        }
                                                    })
                                                }
                                            }
                                            var h = new Object();
                                            h.LocationInformationClass = "BasicLocationInformation";
                                            c.ILocation.GetLocation(h, i)
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (d) {
            if (typeof(console) != a) {
                console.log(d)
            }
            return false
        }
        return c != null
    };
    return b
}();