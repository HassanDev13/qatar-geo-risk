import React, { useState, useEffect, useRef } from 'react';
import { Head } from '@inertiajs/react';
import { useLanguage } from '../Contexts/LanguageContext';
import { MapContainer, TileLayer, Marker, Popup, Circle, useMapEvents, Tooltip } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

// Fix for default marker icons in React-Leaflet when using Vite
delete L.Icon.Default.prototype._getIconUrl;

L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
});

// Qatar Military Bases (Risk Points) - Fallback if API fails
const INITIAL_RISK_POINTS = [
  { lat: 25.1173, lng: 51.3150, name: "Al Udeid Air Base (US/Qatar)", radius: 8000, isBase: true },
];


// Calculate distance between two lat/lng coordinates in km
function getDistance(lat1, lon1, lat2, lon2) {
  var R = 6371; // Radius of the earth in km
  var dLat = deg2rad(lat2-lat1);  
  var dLon = deg2rad(lon2-lon1); 
  var a = 
    Math.sin(dLat/2) * Math.sin(dLat/2) +
    Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
    Math.sin(dLon/2) * Math.sin(dLon/2)
    ; 
  var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
  var d = R * c; 
  return d;
}

function deg2rad(deg) {
  return deg * (Math.PI/180)
}

function calculateRisk(location, riskPoints) {
    if (!location) return null;
    
    // Nearest risk point distance
    let nearestDist = Infinity;
    (riskPoints || INITIAL_RISK_POINTS).forEach(pt => {
        let dist = getDistance(location.lat, location.lng, pt.lat, pt.lng);
        if (dist < nearestDist) nearestDist = dist;
    });

    let distance_score = 0;
    if (nearestDist < 3) distance_score = 100;
    else if (nearestDist < 15) distance_score = 60;
    else if (nearestDist < 50) distance_score = 40;
    else if (nearestDist < 100) distance_score = 20;
    else distance_score = 0;

    let totalRisk = 0;
    // Adjust dummy values based on distance. If distance > 100km, the risk should be near 0
    if (distance_score > 0) {
        let direction_score = 50; 
        let alert_score = 80; 
        let density_score = 70; 
        totalRisk = (distance_score * 0.4) + (direction_score * 0.2) + (alert_score * 0.3) + (density_score * 0.1);
    } else {
        totalRisk = 2; // Very low base risk if nowhere near a danger zone
    }
    
    return {
        score: Math.round(totalRisk),
        distance_score,
        direction_score: distance_score > 0 ? 50 : 0,
        alert_score: distance_score > 0 ? 80 : 0,
        density_score: distance_score > 0 ? 70 : 0,
        nearestDist
    };
}

// Component to handle map events (click to move location, initial locate)
function MapEventsHandler({ location, setLocation, mapRef }) {
  const map = useMapEvents({
    click(e) {
      setLocation(e.latlng);
    },
    locationfound(e) {
      // Intentionally empty: native geolocation takes care of locating now
    }
  });

  useEffect(() => {
    // Disabled GPS auto-locate as requested: map.locate({ enableHighAccuracy: true, setView: false });
    if (mapRef) {
      mapRef.current = map;
    }
  }, [map, mapRef]);

  return location === null ? null : (
    <Marker position={location}>
      <Popup>Selected Location</Popup>
      <Tooltip direction="top" offset={[0, -20]} opacity={1}>Me</Tooltip>
    </Marker>
  );
}


export default function DangerZone() {
    const { t, locale, switchLanguage, isRTL } = useLanguage();
    const [location, setLocation] = useState(null);
    const [locationDetails, setLocationDetails] = useState(t("danger_zone.fetching"));
    const [searchInput, setSearchInput] = useState("");
    const [isSearching, setIsSearching] = useState(false);
    
    // Risk Points state
    const [riskPoints, setRiskPoints] = useState(INITIAL_RISK_POINTS);
    
    // Report Modal State
    const [isReportModalOpen, setIsReportModalOpen] = useState(false);
    const [reportData, setReportData] = useState({ url: "", description: "" });
    const [reportImage, setReportImage] = useState(null);
    const [isSubmittingReport, setIsSubmittingReport] = useState(false);
    
    // Mobile Panel State
    const [isMobilePanelOpen, setIsMobilePanelOpen] = useState(false);
    
    // Create a ref attached to the MapEventsHandler map instance so the parent can access it
    const mapRef = useRef(null);

    useEffect(() => {
        // Fetch risk points from API
        fetch('/api/risk-points')
            .then(res => res.json())
            .then(data => {
                if (data && Array.isArray(data) && data.length > 0) {
                    setRiskPoints([...INITIAL_RISK_POINTS, ...data]);
                }
            })
            .catch(err => console.error("Could not fetch risk points", err));
    }, []);

    const riskData = calculateRisk(location, riskPoints);

    const handleLocateMe = () => {
        setIsSearching(true);
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const newLoc = { 
                        lat: position.coords.latitude, 
                        lng: position.coords.longitude, 
                        isFromSearch: true 
                    };
                    setLocation(newLoc);
                    if (mapRef.current) {
                        mapRef.current.flyTo([newLoc.lat, newLoc.lng], 15);
                    }
                    setIsSearching(false);
                },
                (error) => {
                    console.error("Error obtaining location", error);
                    alert(t("danger_zone.loc_error"));
                    setIsSearching(false);
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        } else {
            alert(t("danger_zone.loc_error"));
            setIsSearching(false);
        }
    };

    const handleSearch = async (e) => {
        e.preventDefault();
        if (!searchInput) return;

        setIsSearching(true);
        let finalLat = null;
        let finalLng = null;
        
        try {
            // Check if it's a URL (like google maps)
            if (searchInput.includes("http://") || searchInput.includes("https://")) {
                const res = await fetch('/api/parse-location', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ url: searchInput })
                });
                const data = await res.json();
                
                if (data.lat && data.lng) {
                    finalLat = data.lat;
                    finalLng = data.lng;
                } else {
                    alert(t("danger_zone.error_parse"));
                }
            } else {
                alert(t("danger_zone.error_paste"));
            }
            
            if (finalLat !== null && finalLng !== null) {
                const newLoc = { lat: finalLat, lng: finalLng, isFromSearch: true };
                setLocation(newLoc);
                if (mapRef.current) {
                    mapRef.current.flyTo([newLoc.lat, newLoc.lng], 15);
                }
            }
        } catch (err) {
            console.error(err);
            alert("Error trying to search location.");
        } finally {
            setIsSearching(false);
        }
    };

    const submitReport = async (e) => {
        e.preventDefault();
        if (!reportData.url || !reportData.description) return;
        setIsSubmittingReport(true);
        
        const formData = new FormData();
        formData.append("url", reportData.url);
        formData.append("description", reportData.description);
        if (reportImage) {
            formData.append("image", reportImage);
        }

        try {
            const res = await fetch("/api/risk-points", {
                method: "POST",
                body: formData,
            });
            const data = await res.json();
            if (res.ok) {
                alert(t("danger_zone.success"));
                setIsReportModalOpen(false);
                setReportData({ url: "", description: "" });
                setReportImage(null);
            } else {
                alert(data.error || "Failed to submit report. Please check the provided link and try again.");
            }
        } catch (error) {
            console.error(error);
            alert("Error submitting the report.");
        } finally {
            setIsSubmittingReport(false);
        }
    };

    useEffect(() => {
        if (!location) return;
        
        // If it's a GPS click or Map click, we don't fetch details to save API calls and ensure we only process URLs
        if (!location.isFromSearch) {
            setLocationDetails(t("danger_zone.selected_map"));
            return;
        }

        setLocationDetails(t("danger_zone.fetching"));
        
        // OpenStreetMap Nominatim reverse geocoding
        // Using URL parameter for language to avoid CORS preflight issues
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${location.lat}&lon=${location.lng}&zoom=18&addressdetails=1&accept-language=en`)
            .then(res => res.json())
            .then(data => {
                if (data && data.display_name) {
                    const addr = data.address || {};
                    // Build a concise, readable address
                    const street = addr.road || addr.pedestrian || addr.path || "";
                    const area = addr.suburb || addr.neighbourhood || addr.city_district || addr.county || "";
                    const city = addr.city || addr.town || addr.village || addr.state || addr.country || "Unknown Region";
                    
                    let parts = [];
                    if (street) parts.push(street);
                    if (area && area !== street) parts.push(area);
                    if (city && city !== area && city !== street) parts.push(city);
                    
                    // Fallback to display_name if parts are somehow empty
                    setLocationDetails(parts.length > 0 ? parts.join(" • ") : data.display_name);
                    } else if (data && data.error) {
                    // Sometimes Nominatim returns { "error": "Unable to geocode" }
                    setLocationDetails(t("danger_zone.uncharted"));
                } else {
                    setLocationDetails(t("danger_zone.unavailable"));
                }
            })
            .catch(err => {
                console.error("Geocoding error:", err);
                setLocationDetails(t("danger_zone.unavailable"));
            });
    }, [location]);

    let riskLevelText = t("danger_zone.low");
    let riskColorClass = "bg-green-500";
    let textColorClass = "text-green-400";
    let mapColor = "green";
    
    if (riskData && riskData.score >= 70) {
        riskLevelText = t("danger_zone.high");
        riskColorClass = "bg-red-500";
        textColorClass = "text-red-400";
        mapColor = "red";
    } else if (riskData && riskData.score >= 40) {
        riskLevelText = t("danger_zone.medium");
        riskColorClass = "bg-yellow-500";
        textColorClass = "text-yellow-400";
        mapColor = "yellow";
    }

    return (
        <div className={`relative w-full h-screen bg-gray-900 overflow-hidden ${isRTL ? 'font-arabic' : 'font-sans'}`} dir={isRTL ? 'rtl' : 'ltr'}>
            <Head title={t("danger_zone.title")} />
            
            {/* Map Layer */}
            <div className="absolute inset-0 z-0">
                <MapContainer center={[25.2854, 51.5310]} zoom={9} zoomControl={false} attributionControl={false} scrollWheelZoom={true} style={{ height: "100%", width: "100%", zIndex: 0 }}>
                    <TileLayer
                        attribution=''
                        url="https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png"
                    />
                    
                    <MapEventsHandler location={location} setLocation={setLocation} mapRef={mapRef}/>


                    {/* Threat Zones */}
                    {riskPoints.map((pt, i) => {
                        const isBase = pt.isBase === true;
                        const zoneColor = isBase ? '#ef4444' : '#f59e0b'; // Red for Base, Amber for User Reported
                        
                        return (
                        <Circle 
                            key={i} 
                            center={[pt.lat, pt.lng]} 
                            radius={pt.radius || 3000} 
                            pathOptions={{ color: zoneColor, fillColor: zoneColor, fillOpacity: isBase ? 0.1 : 0.2, weight: 1 }}
                        >
                            <Popup minWidth={250} maxWidth={300} className="danger-popup">
                                <div className="p-1">
                                    <h3 className="font-bold text-base mb-1 text-gray-900 border-b pb-1">{pt.name}</h3>
                                    {pt.description && (
                                        <p className="text-sm text-gray-700 font-medium mb-3 mt-2 line-clamp-3 leading-relaxed">
                                            {pt.description}
                                        </p>
                                    )}
                                    {pt.image && (
                                        <div className="w-full mt-2 rounded bg-gray-100 p-1 border">
                                            <a href={`/storage/${pt.image}`} target="_blank" rel="noreferrer" className="block relative group">
                                                <img src={`/storage/${pt.image}`} alt="Evidence" className="w-full h-auto max-h-32 object-cover object-top rounded brightness-90 group-hover:brightness-100 transition-all" />
                                                <div className="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/40 rounded">
                                                    <span className="text-white text-xs font-bold drop-shadow-md flex items-center gap-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" className="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                                        {t("danger_zone.view_proof")}
                                                    </span>
                                                </div>
                                            </a>
                                        </div>
                                    )}
                                    <div className="mt-3 text-xs text-gray-400 font-mono">
                                        [{pt.lat.toFixed(4)}, {pt.lng.toFixed(4)}]
                                    </div>
                                </div>
                            </Popup>
                            <Tooltip sticky>
                                <div className="text-center px-1">
                                    <strong className="block text-sm whitespace-nowrap">{pt.name}</strong>
                                    {pt.description && <span className="text-xs text-gray-600 mt-1 block max-w-[200px] whitespace-normal">{pt.description}</span>}
                                    <span className="text-[10px] text-blue-500 mt-1 block font-bold uppercase">(Click for evidence details)</span>
                                </div>
                            </Tooltip>
                        </Circle>
                        );
                    })}

                    {/* User Risk Radius Indicator */}
                    {location && (
                        <Circle center={location} radius={3000} pathOptions={{ color: mapColor, fillColor: mapColor, fillOpacity: 0.2, weight: 2 }}>
                            <Tooltip sticky>My Risk Zone</Tooltip>
                        </Circle>
                    )}
                    

                </MapContainer>
            </div>

            {/* Mobile Toggle Button (Visible only on small screens when panel is closed) */}
            <div className={`md:hidden absolute top-4 z-20 pointer-events-auto transition-all duration-300 ${isMobilePanelOpen ? 'opacity-0 scale-95 pointer-events-none' : 'opacity-100 scale-100'}`} style={{ [isRTL ? 'right' : 'left']: '1rem' }}>
                <button
                    onClick={() => setIsMobilePanelOpen(true)}
                    className="bg-[#8A1538]/90 text-white p-3 rounded-xl shadow-2xl hover:bg-[#6c102c] transition flex items-center justify-center border border-white/20 backdrop-blur-md"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            {/* UI Overlay */}
            <div className={`absolute top-0 w-full h-full pointer-events-none z-10 flex flex-col md:flex-row p-4 md:p-8 justify-between ${isRTL ? 'right-0' : 'left-0'}`}>
                {/* Header / Main Panel */}
                <div className={`bg-[#8A1538]/90 backdrop-blur-xl border border-white/20 text-white p-6 rounded-2xl shadow-2xl pointer-events-auto h-fit w-full max-w-sm mb-4 md:mb-0 transform transition-all duration-500 relative overflow-y-auto max-h-[90vh] md:max-h-none md:translate-y-0 md:opacity-100 md:scale-100 ${isMobilePanelOpen ? 'translate-y-0 opacity-100 scale-100 pointer-events-auto' : '-translate-y-[150%] opacity-0 scale-95 pointer-events-none md:pointer-events-auto'}`}>
                    
                    {/* Mobile Close Button */}
                    <button 
                        onClick={() => setIsMobilePanelOpen(false)}
                        className={`md:hidden absolute top-4 ${isRTL ? 'left-4' : 'right-4'} text-white/50 hover:text-white bg-white/10 hover:bg-white/20 p-1.5 rounded-full transition-colors`}
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>

                    {/* Language Switcher */}
                    <button
                        onClick={() => switchLanguage(locale === 'en' ? 'ar' : 'en')}
                        className={`absolute top-4 ${isRTL ? 'left-[3.5rem]' : 'right-[3.5rem]'} md:${isRTL ? 'left-4' : 'right-4'} text-sm font-bold bg-white/10 hover:bg-white/20 px-3 py-1 rounded-full transition-colors`}
                    >
                        {locale === 'en' ? 'عربي' : 'EN'}
                    </button>

                    <div className="flex items-center gap-3 mb-2">
                        <div className="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <h1 className="text-xl font-bold tracking-tight">{t("danger_zone.title")}</h1>
                    </div>
                    <p className={`text-gray-300 text-sm mb-1 pl-11 rtl:pl-0 rtl:pr-11`}>{t("danger_zone.subtitle")}</p>
                    <p className={`text-white/60 text-xs mb-4 pl-11 rtl:pl-0 rtl:pr-11 font-medium italic`}>"{t("danger_zone.quote")}"</p>
                    
                    {/* Search Field */}
                    <div className={`mb-4 pl-11 rtl:pl-0 rtl:pr-11 flex gap-2 w-full max-w-[calc(100%-2.75rem)] relative`}>
                        <form onSubmit={handleSearch} className="flex-grow relative">
                            <input
                                type="text"
                                value={searchInput}
                                onChange={(e) => setSearchInput(e.target.value)}
                                className="w-full bg-white/10 border border-white/20 text-white placeholder-gray-300 text-sm rounded-lg ltr:pr-10 ltr:pl-3 rtl:pl-10 rtl:pr-3 py-2 focus:outline-none focus:ring-1 focus:ring-white focus:border-white"
                                placeholder={t("danger_zone.search_placeholder")}
                            />
                            <button 
                                type="submit" 
                                disabled={isSearching}
                                className={`absolute ${isRTL ? 'left-0' : 'right-0'} top-0 bottom-0 px-3 flex items-center justify-center text-white/70 hover:text-white disabled:opacity-50`}
                            >
                                {isSearching ? (
                                    <div className="animate-spin h-4 w-4 border-2 border-white/20 border-t-white rounded-full"></div>
                                ) : (
                                    <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                )}
                            </button>
                        </form>
                        <button 
                            type="button"
                            onClick={handleLocateMe}
                            title={t("danger_zone.locate_me")}
                            disabled={isSearching}
                            className="bg-white/10 hover:bg-white/20 border border-white/20 text-white rounded-lg px-3 py-2 flex items-center justify-center transition-colors disabled:opacity-50"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>

                    {location && (
                        <div className={`mb-4 pl-11 rtl:pl-0 rtl:pr-11`}>
                            <div className="flex shadow-sm bg-white/10 border border-white/20 rounded-lg p-2.5 items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4 mt-0.5 text-white/70 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                <span className={`text-sm font-medium line-clamp-2 ${locationDetails.includes('...') ? 'text-gray-300 animate-pulse' : 'text-white'}`}>
                                    {locationDetails}
                                </span>
                            </div>
                        </div>
                    )}
                    
                    {location && (
                        <p className={`text-xs text-white/70 mb-4 pl-11 rtl:pl-0 rtl:pr-11 animate-pulse font-medium`}>{t("danger_zone.tip")}</p>
                    )}
                    
                    {!location && (
                        <div className="flex flex-col justify-center items-center py-10 bg-black/20 rounded-xl border border-white/10 mx-6 md:mx-0">
                            <span className="text-sm text-gray-200 font-medium tracking-wide">{t("danger_zone.awaiting_location")}</span>
                            <p className="text-xs text-white/50 mt-2 text-center px-4">{t("danger_zone.awaiting_desc")}</p>
                        </div>
                    )}

                    {location && riskData && (
                        <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500 mt-2">
                            <div className="flex items-end justify-between px-1">
                                <span className="text-gray-200 font-medium">{t("danger_zone.risk_score")}</span>
                                <div className={`text-${isRTL ? 'left' : 'right'}`}>
                                    <span className="text-5xl font-extrabold tracking-tighter">{riskData.score}</span>
                                    <span className="text-gray-300 font-medium ml-1">/100</span>
                                </div>
                            </div>

                            <div className="w-full h-2 rounded-full mb-1 bg-gray-800 overflow-hidden shadow-inner">
                                <div className={`h-full ${riskColorClass} transition-all duration-1000 ease-out`} style={{ width: `${riskData.score}%` }}></div>
                            </div>

                            <div className="grid grid-cols-2 gap-3 text-sm mt-4">
                                <div className="bg-white/10 border border-white/10 p-3 rounded-xl hover:bg-white/20 transition-colors">
                                    <span className="block text-gray-200 text-[10px] font-bold uppercase tracking-wider mb-1">{t("danger_zone.proximity")}</span>
                                    <span className="font-semibold text-lg">{riskData.distance_score}</span>
                                    <span className="text-xs text-gray-300 block mt-0.5">({Math.round(riskData.nearestDist)} km)</span>
                                </div>
                                <div className="bg-white/10 border border-white/10 p-3 rounded-xl hover:bg-white/20 transition-colors">
                                    <span className="block text-gray-200 text-[10px] font-bold uppercase tracking-wider mb-1">{t("danger_zone.alert_level")}</span>
                                    <span className="font-semibold text-yellow-300 text-lg">{t("danger_zone.alert_elevated")}</span>
                                    <span className="text-xs text-gray-300 block mt-0.5">{t("danger_zone.alert_regional")}</span>
                                </div>
                            </div>

                            <div className="bg-white/10 border border-white/20 rounded-xl p-4 mt-6 backdrop-blur-sm relative overflow-hidden">
                                <div className={`absolute top-0 ${isRTL ? 'right-0' : 'left-0'} w-1 h-full ${riskColorClass}`}></div>
                                
                                <h3 className={`font-bold text-lg mb-2 flex items-center gap-2 ${textColorClass}`}>
                                    <span className={`relative flex h-3 w-3`}>
                                      <span className={`animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 ${riskColorClass}`}></span>
                                      <span className={`relative inline-flex rounded-full h-3 w-3 ${riskColorClass}`}></span>
                                    </span>
                                    {t("danger_zone.level", {level: riskLevelText})}
                                </h3>
                                <p className="text-sm text-gray-100 leading-relaxed font-medium">
                                    {riskLevelText === t("danger_zone.high") ? t("danger_zone.high_desc") :
                                     riskLevelText === t("danger_zone.medium") ? t("danger_zone.medium_desc") :
                                     t("danger_zone.low_desc")}
                                </p>
                            </div>
                        </div>
                    )}
                    
                    <button
                        onClick={() => setIsReportModalOpen(true)}
                        className="mt-4 w-full bg-white/10 hover:bg-white/20 border border-white/20 text-white py-2 rounded-xl text-sm font-semibold transition-all flex items-center justify-center gap-2"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        {t("danger_zone.report_btn")}
                    </button>
                </div>
            </div>
            
            {/* Report Modal */}
            {isReportModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm pointer-events-auto" dir={isRTL ? 'rtl' : 'ltr'}>
                    <div className="bg-[#8A1538] border border-white/20 rounded-2xl w-full max-w-md p-6 shadow-2xl relative">
                        <button 
                            onClick={() => setIsReportModalOpen(false)}
                            className={`absolute top-4 ${isRTL ? 'left-4' : 'right-4'} text-white/50 hover:text-white`}
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                        <h2 className="text-xl font-bold text-white mb-4">{t("danger_zone.report_title")}</h2>
                        <form onSubmit={submitReport} className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-white/80 mb-1">{t("danger_zone.map_link")}</label>
                                <input 
                                    type="url" 
                                    required
                                    value={reportData.url}
                                    onChange={e => setReportData({...reportData, url: e.target.value})}
                                    placeholder="https://maps.app.goo.gl/..."
                                    className="w-full bg-white/10 border border-white/20 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-white placeholder-white/50"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-white/80 mb-1">{t("danger_zone.desc")}</label>
                                <textarea 
                                    required
                                    value={reportData.description}
                                    onChange={e => setReportData({...reportData, description: e.target.value})}
                                    rows="3"
                                    className="w-full bg-white/10 border border-white/20 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-white"
                                ></textarea>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-white/80 mb-1">{t("danger_zone.image_ev")}</label>
                                <input 
                                    type="file" 
                                    accept="image/*"
                                    onChange={e => setReportImage(e.target.files[0])}
                                    className="w-full text-sm text-white/70 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-white/20 file:text-white hover:file:bg-white/30"
                                />
                                {reportImage && (
                                    <div className="mt-2 w-full max-h-32 overflow-hidden rounded-lg border border-white/20 relative">
                                        <img src={URL.createObjectURL(reportImage)} alt="Preview" className="w-full h-auto object-cover" />
                                    </div>
                                )}
                            </div>
                            <button 
                                type="submit" 
                                disabled={isSubmittingReport}
                                className="w-full bg-white hover:bg-gray-200 text-[#8A1538] py-2 rounded-lg font-bold transition-colors disabled:opacity-50 mt-2"
                            >
                                {isSubmittingReport ? t("danger_zone.submitting") : t("danger_zone.submit")}
                            </button>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
