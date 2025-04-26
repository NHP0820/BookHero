<?php
require '../_base.php';

$_title = 'Store Locator';
include '../_head.php';
?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
}

.container {
    display: flex;
    flex-wrap: wrap;
}

.location-list {
    flex: 1 1 300px;
    max-width: 400px;
    background: #f7f7f7;
    padding: 20px;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    overflow-y: auto;
    height: 100vh;
}

.location-item {
    padding: 10px;
    margin-bottom: 10px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

#map {
    flex: 2 1 600px;
    height: 100vh;
}

.search-bar {
    margin-bottom: 15px;
}

.search-bar input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
}
</style>

<div class="container">
    <div class="location-list">
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Search store name..." oninput="filterLocations()">
        </div>
        <div id="locationList">
            <!-- Locations will be inserted here -->
        </div>
    </div>

    <div id="map"></div>
</div>

<script>
let map;
const locations = [
    {name: 'BookHero HQ', lat: 3.139, lng: 101.6869, address: 'Kuala Lumpur, Malaysia'},
    {name: 'BookHero Penang', lat: 5.4141, lng: 100.3288, address: 'George Town, Penang'},
    {name: 'BookHero Johor', lat: 1.4927, lng: 103.7414, address: 'Johor Bahru, Johor'},
    {name: 'BookHero Sabah', lat: 5.9788, lng: 116.0753, address: 'Kota Kinabalu, Sabah'},
    {name: 'BookHero Sarawak', lat: 1.5533, lng: 110.3592, address: 'Kuching, Sarawak'},
];

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 3.139, lng: 101.6869 },
        zoom: 6
    });

    locations.forEach((loc, index) => {
        const marker = new google.maps.Marker({
            position: { lat: loc.lat, lng: loc.lng },
            map,
            title: loc.name
        });

        const infowindow = new google.maps.InfoWindow({
            content: `<strong>${loc.name}</strong><br>${loc.address}`
        });

        marker.addListener('click', () => {
            infowindow.open(map, marker);
        });
    });

    renderLocationList();
}

function renderLocationList() {
    const list = document.getElementById('locationList');
    list.innerHTML = '';
    locations.forEach(loc => {
        const div = document.createElement('div');
        div.className = 'location-item';
        div.innerHTML = `<strong>${loc.name}</strong><br>${loc.address}`;
        list.appendChild(div);
    });
}

function filterLocations() {
    const keyword = document.getElementById('searchInput').value.toLowerCase();
    const list = document.getElementById('locationList');
    list.innerHTML = '';
    locations.filter(loc => loc.name.toLowerCase().includes(keyword)).forEach(loc => {
        const div = document.createElement('div');
        div.className = 'location-item';
        div.innerHTML = `<strong>${loc.name}</strong><br>${loc.address}`;
        list.appendChild(div);
    });
}
</script>

<!-- Google Maps Script -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyChgZkLQEV_R10sM3w74buvjgMmzNw3JQE&callback=initMap" async defer></script>

<?php
include '../_foot.php';
?>
