let currentInfoWindow = null;
let map;
let markers = []; // Track markers so we can clear them

function initMap() {
    const fallbackCoords = { lat: 31.9539, lng: 35.9106 };
    map = new google.maps.Map(document.getElementById("map"), {
        mapId: '28eb517e46874f3e19099b0b',
        zoom: 12,
        center: fallbackCoords,
        streetViewControl: false,
        mapTypeControl: false,
        fullscreenControl: false
    });

    // Initial load from the global variable defined in PHP
    if (typeof providersData !== 'undefined') {
        renderProviders(providersData);
    }

    // Set up AJAX Search
    const searchInput = document.getElementById('search-input');
    const locationSelect = document.getElementById('location-select');

    const performSearch = () => {
        const q = searchInput.value;
        const loc = locationSelect.value;

        fetch(`../actions/search_providers.php?q=${encodeURIComponent(q)}&loc=${encodeURIComponent(loc)}`)
            .then(response => response.json())
            .then(data => {
                renderProviders(data);
            })
            .catch(err => console.error("Search Error:", err));
    };

    searchInput.addEventListener('input', performSearch);
    locationSelect.addEventListener('change', performSearch);

    // RESTORED: Client Geolocation (User's Blue Dot)
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((position) => {
            const userCoords = { lat: position.coords.latitude, lng: position.coords.longitude };
            map.setCenter(userCoords);
            new google.maps.Marker({
                position: userCoords,
                map: map,
                title: "Your Location",
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 8,
                    fillColor: '#007bff',
                    fillOpacity: 1,
                    strokeWeight: 0,
                },
                zIndex: 999
            });
        });
    }
}

function renderProviders(data) {
    markers.forEach(m => m.setMap(null));
    markers = [];

    const listContainer = document.getElementById('provider-list');
    listContainer.innerHTML = '';

    if (data.length === 0) {
        listContainer.innerHTML = '<p class="text-center mt-5">No experts found.</p>';
        return;
    }

    data.forEach(provider => {
        const lat = parseFloat(provider.latitude);
        const lng = parseFloat(provider.longitude);

        // 1. Sidebar Card (Modified to include buttons)
        const card = document.createElement('div');
        card.className = 'provider-card-v4';
        card.innerHTML = `
            <img class="card-image-v4" src="${provider.image}">
            <div class="card-details-v4">
                <h5>${provider.full_name}</h5>
                <p class="text-secondary mb-1">${provider.services}</p>
                <p class="mb-2"><i class="bi bi-geo-alt me-1"></i>${provider.address || 'N/A'}</p>
                
                <div class="d-flex gap-2 mt-2">
                    <a href="provider_profile_page.php?id=${provider.provider_id}" 
                       class="btn btn-sm btn-outline-primary flex-grow-1 action-btn">
                       View Profile
                    </a>
                    <a href="direct_request.php?provider_id=${provider.provider_id}&name=${encodeURIComponent(provider.full_name)}&service=${encodeURIComponent(provider.services)}" 
                       class="btn btn-sm btn-success flex-grow-1 action-btn">
                       Request
                    </a>
                </div>
            </div>
        `;
        
        // Map interaction logic
        card.onclick = (e) => {
            // Only move map if the click wasn't on one of the buttons
            if (!e.target.classList.contains('action-btn')) {
                if (!isNaN(lat)) {
                    map.setCenter({ lat, lng });
                    map.setZoom(15);
                }
            }
        };

        listContainer.appendChild(card);

        // 2. Map Marker & InfoWindow Content
        if (!isNaN(lat)) {
            const marker = new google.maps.Marker({
                position: { lat, lng },
                map: map,
                title: provider.full_name,
                icon: { url: provider.image, scaledSize: new google.maps.Size(40, 40) }
            });

            const contentString = `
                <div style="text-align:center; min-width: 180px; padding: 10px;">
                    <img src="${provider.image}" style="width:60px;height:60px;border-radius:50%;object-fit:cover;margin-bottom:8px;"><br>
                    <strong style="font-size:16px;">${provider.full_name}</strong><br>
                    <p class="text-muted mb-2">${provider.services}</p>
                    <div class="d-grid gap-2">
                        <a href="provider_profile_page.php?id=${provider.provider_id}" class="btn btn-sm btn-outline-primary">View Profile</a>
                        <a href="direct_request.php?provider_id=${provider.provider_id}&name=${encodeURIComponent(provider.full_name)}&service=${encodeURIComponent(provider.services)}" class="btn btn-sm btn-success">
                            <i class="bi bi-lightning-fill"></i> Direct Quote
                        </a>
                    </div>
                </div>
            `;

            const infowindow = new google.maps.InfoWindow({
                content: contentString
            });

            marker.addListener('click', () => {
                if (currentInfoWindow) currentInfoWindow.close();
                infowindow.open(map, marker);
                currentInfoWindow = infowindow;
            });

            markers.push(marker);
        }
    });
}