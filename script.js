document.addEventListener('DOMContentLoaded', () => {

    // --- Mobile Menu Toggle ---
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
    }

    // --- Contact Form Handler (Mailto) ---
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const subject = document.getElementById('subject').value;
            const message = document.getElementById('message').value;

            const finalSubject = encodeURIComponent(`[Website Inquiry] ${subject} - ${name}`);
            const finalBody = encodeURIComponent(`Name: ${name}\nEmail: ${email}\n\nMessage:\n${message}`);

            window.location.href = `mailto:info@freedomdiscovery.net?subject=${finalSubject}&body=${finalBody}`;
        });
    }

    // --- Back to Top Button Logic ---
    const backToTopBtn = document.getElementById("backToTop");

    window.onscroll = function () {
        scrollFunction();
    };

    function scrollFunction() {
        // Show button after scrolling down 300px
        if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
            backToTopBtn.style.display = "block";
        } else {
            backToTopBtn.style.display = "none";
        }
    }

    // Scroll to top when clicked
    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth' // Smooth scrolling animation
        });
    });

    // --- Auto-Update Copyright Year ---
    const yearSpan = document.querySelector('#currentYear');
    if (yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }

    // --- Scroll Animation Observer ---
    const observerOptions = {
        threshold: 0.15, // Trigger when 15% of the element is visible
        rootMargin: "0px 0px -50px 0px" // Trigger slightly before the bottom
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                // Optional: Stop observing once animated (so it doesn't fade out again)
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Find all elements to animate
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    animatedElements.forEach((el) => observer.observe(el));

    // --- Floating Contact Widget Injection ---
    const widgetDiv = document.createElement('div');
    widgetDiv.className = 'floating-contact-widget';
    widgetDiv.innerHTML = `
        <a href="https://wa.me/60124883300" target="_blank" class="contact-icon whatsapp" title="Chat on WhatsApp" aria-label="Chat on WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </a>
    `;
    document.body.appendChild(widgetDiv);

    // --- PROMO POPUP LOGIC ---
    const POSTER_PATH = 'assets/promo-poster.jpg';
    const modal = document.getElementById('promoModal');
    const closeBtn = document.querySelector('.close-btn');
    const promoImage = document.getElementById('promoImage');

    if (modal && promoImage) {
        // Function to Open Modal
        function showModal() {
            // Cache Busting: Add timestamp to force reload image
            const uniqueSrc = POSTER_PATH + '?v=' + new Date().getTime();

            // Check if image exists (fail-safe)
            const imgCheck = new Image();
            imgCheck.src = uniqueSrc;

            imgCheck.onload = function () {
                promoImage.src = uniqueSrc;
                modal.style.display = 'flex';
                // optional: sessionStorage.setItem('promoShown', 'true');
            };

            imgCheck.onerror = function () {
                console.log("No promo poster found, skipping popup.");
            };
        }

        // Logic: Show ONCE per session
        if (!sessionStorage.getItem('promoShown')) {
            // Delay slightly for better UX
            setTimeout(showModal, 1500);
        }

        // Close Events
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                modal.style.display = 'none';
                sessionStorage.setItem('promoShown', 'true'); // Mark as shown when closed
            });
        }

        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
                sessionStorage.setItem('promoShown', 'true');
            }
        });
    }

    // --- DYNAMIC EVENTS RENDERING ---
    const eventsList = document.querySelector('.events-list');
    if (eventsList) {
        fetch('data/events.json')
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    eventsList.innerHTML = '<p class="text-center">No upcoming events currently scheduled.</p>';
                    return;
                }
                eventsList.innerHTML = ''; // Clear placeholder content

                data.forEach((event, index) => {
                    // Simple date parsing for badge
                    // Assuming format "25 October 2025" or similar
                    const parts = event.date.split(' ');
                    const day = parts[0] || 'TBD';
                    const month = parts[1] ? parts[1].substring(0, 3).toUpperCase() : 'UPC';

                    // HTML Construction
                    const eventCard = document.createElement('div');
                    eventCard.className = `event-ticket animate-on-scroll`;
                    // Stagger animation slightly
                    eventCard.style.transitionDelay = `${index * 0.1}s`;

                    eventCard.innerHTML = `
                        <div class="date-badge">
                            <span class="month">${month}</span>
                            <span class="day">${day}</span>
                        </div>
                        <div class="event-details">
                            <h3>${event.name}</h3>
                            <div class="meta-info">
                                <span><i class="far fa-clock"></i> ${event.date}</span>
                                <span><i class="fas fa-map-marker-alt"></i> ${event.location}</span>
                            </div>
                            <p>${event.description}</p>
                        </div>
                        <div class="event-action">
                            <a href="contact.html" class="btn btn-outline-blue">Register Now</a>
                        </div>
                    `;

                    eventsList.appendChild(eventCard);

                    // Observe new element for scroll animation
                    if (typeof observer !== 'undefined') {
                        observer.observe(eventCard);
                    }
                });
            })
            .catch(err => {
                console.error('Error loading events:', err);
                eventsList.innerHTML = '<p class="text-center">Unable to load events at this time.</p>';
            });
    }
});