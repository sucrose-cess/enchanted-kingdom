<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enchanted Kingdom | Philippine Tourist Destination</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="stars-overlay"></div>

    <header class="navbar" id="navbar">
        <div class="logo">EK Magic</div>
        <nav>
            <ul class="nav-links">
                <li><a href="#home">Realm</a></li>
                <li><a href="#attractions">Wonders</a></li>
                <li><a href="#tickets">Summon Tickets</a></li>
            </ul>
        </nav>
        <button class="cta-btn" onclick="window.location.href='#tickets'">Enter Now</button>
    </header>

    <main>
        <section id="home" class="hero">
            <div class="hero-slideshow">
                <div class="slide slide-1"></div>
                <div class="slide slide-2"></div>
                <div class="slide slide-3"></div>
                <div class="slide slide-1-dup"></div>
            </div>
            
            <div class="hero-overlay"></div>

            <div class="hero-content floating">
                <h1>The Magic Lives Here</h1>
                <p>Step into a world of whimsical thrills and enchanting memories.</p>
                <a href="#tickets" class="primary-btn">Begin Your Journey</a>
            </div>
        </section>

        <section id="attractions" class="attractions">
            <h2 class="glowing-text">Featured Wonders of the Philippines</h2>
            
            <div class="card-grid">
                <a href="space-shuttle.html" class="card-link">
                    <div class="card">
                        <div class="card-img-box">
                            <img src="Images/Attractions/shuttle-thumb.png" alt="Space Shuttle">
                        </div>
                        <div class="card-content">
                            <h3>Space Shuttle</h3>
                            <p>Defy gravity on the realm's most iconic 11-story looping coaster.</p>
                        </div>
                    </div>
                </a>

                <a href="jungle-log-jam.html" class="card-link">
                    <div class="card">
                        <div class="card-img-box">
                            <img src="Images/Attractions/log-jam-thumb.png" alt="Jungle Log Jam">
                        </div>
                        <div class="card-content">
                            <h3>Jungle Log Jam</h3>
                            <p>Take a thrilling plunge down a rushing water flume into the jungle.</p>
                        </div>
                    </div>
                </a>

                <a href="rio-grande-rapids.html" class="card-link">
                    <div class="card">
                        <div class="card-img-box">
                            <img src="Images/Attractions/rapids-thumb.png" alt="Rio Grande Rapids">
                        </div>
                        <div class="card-content">
                            <h3>Rio Grande Rapids</h3>
                            <p>Brave the rushing waters and sudden drops of this wild, enchanting river.</p>
                        </div>
                    </div>
                </a>

                <a href="grand-carousel.html" class="card-link">
                    <div class="card">
                        <div class="card-img-box">
                            <img src="Images/Attractions/carousel-thumb.png" alt="Grand Carousel">
                        </div>
                        <div class="card-content">
                            <h3>Grand Carousel</h3>
                            <p>Enjoy a classic, whimsical ride on beautifully crafted steeds.</p>
                        </div>
                    </div>
                </a>

                <a href="anchors-away.html" class="card-link">
                    <div class="card">
                        <div class="card-img-box">
                            <img src="Images/Attractions/anchors-thumb.png" alt="Anchors Away">
                        </div>
                        <div class="card-content">
                            <h3>Anchors Away</h3>
                            <p>Sail the celestial seas on this giant swinging galleon.</p>
                        </div>
                    </div>
                </a>

                <a href="flying-fiesta.html" class="card-link">
                    <div class="card">
                        <div class="card-img-box">
                            <img src="Images/Attractions/fiesta-thumb.png" alt="Flying Fiesta">
                        </div>
                        <div class="card-content">
                            <h3>Flying Fiesta</h3>
                            <p>Soar through the air on a magical, high-speed giant swing ride.</p>
                        </div>
                    </div>
                </a>
            </div>
        </section>

        <section id="tickets" class="inquiry-section">
            <img src="Wizard.png" alt="Eldar the Wizard" class="wizard-side-img">

            <div class="ticket-wrapper">
                <h2 class="glowing-text">Summon Your Tickets</h2>
                
                <form id="inquiryForm" class="inquiry-form glass-panel" action="book.php" method="POST">
                    <div class="form-group">
                        <label for="name">Traveler's Name</label>
                        <input type="text" id="name" name="name" placeholder="Enter your full name">
                        <small class="error-msg"></small>
                    </div>
                    <div class="form-group">
                        <label for="email">Celestial Owl Mail (Email)</label>
                        <input type="email" id="email" name="email" placeholder="traveler@realm.com">
                        <small class="error-msg"></small>
                    </div>
                    <button type="submit" class="submit-btn">Cast Inquiry Spell</button>
                </form>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 Enchanted Kingdom Fan Portal | Santa Rosa, Laguna, Philippines. May the magic be with you.</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>
