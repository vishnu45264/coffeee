document.addEventListener('DOMContentLoaded', function() {
    // Initialize all tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Gallery lightbox functionality
    const galleryItems = document.querySelectorAll('.gallery-item');
    const lightbox = document.querySelector('.lightbox');
    
    if (galleryItems.length > 0 && lightbox) {
        const lightboxImg = document.querySelector('.lightbox-img');
        const closeBtn = document.querySelector('.close-lightbox');
        const nextBtn = document.querySelector('.next-img');
        const prevBtn = document.querySelector('.prev-img');
        let currentIndex = 0;
        const images = [];

        // Collect all gallery images
        galleryItems.forEach((item, index) => {
            const img = item.querySelector('img');
            images.push(img.src);

            // Open lightbox on click
            item.addEventListener('click', function() {
                currentIndex = index;
                lightboxImg.src = images[currentIndex];
                lightbox.style.display = 'block';
                document.body.style.overflow = 'hidden'; // Prevent scrolling
            });
        });

        // Close lightbox
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                lightbox.style.display = 'none';
                document.body.style.overflow = 'auto'; // Enable scrolling
            });
        }

        // Navigate to next image
        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                currentIndex = (currentIndex + 1) % images.length;
                lightboxImg.src = images[currentIndex];
            });
        }

        // Navigate to previous image
        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                currentIndex = (currentIndex - 1 + images.length) % images.length;
                lightboxImg.src = images[currentIndex];
            });
        }

        // Close lightbox with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && lightbox.style.display === 'block') {
                lightbox.style.display = 'none';
                document.body.style.overflow = 'auto'; // Enable scrolling
            }
        });
    }

    // Order form quantity and total calculation
    const quantityInputs = document.querySelectorAll('.order-quantity');
    if (quantityInputs.length > 0) {
        quantityInputs.forEach(input => {
            input.addEventListener('change', updateTotal);
            input.addEventListener('keyup', updateTotal);
        });
    }

    function updateTotal() {
        const orderRow = this.closest('.order-row');
        const priceElement = orderRow.querySelector('.item-price');
        const totalElement = orderRow.querySelector('.item-total');
        
        if (priceElement && totalElement) {
            const price = parseFloat(priceElement.getAttribute('data-price'));
            const quantity = parseInt(this.value) || 0;
            const total = price * quantity;
            
            totalElement.textContent = '$' + total.toFixed(2);
            totalElement.setAttribute('data-total', total.toFixed(2));
            
            // Update order form grand total
            calculateGrandTotal();
        }
    }

    function calculateGrandTotal() {
        const totalElements = document.querySelectorAll('.item-total');
        let grandTotal = 0;
        
        totalElements.forEach(elem => {
            grandTotal += parseFloat(elem.getAttribute('data-total')) || 0;
        });
        
        const grandTotalElement = document.getElementById('grand-total');
        if (grandTotalElement) {
            grandTotalElement.textContent = '$' + grandTotal.toFixed(2);
            
            // Update hidden input for form submission
            const hiddenTotal = document.getElementById('total-amount');
            if (hiddenTotal) {
                hiddenTotal.value = grandTotal.toFixed(2);
            }
        }
    }

    // Admin dashboard charts (if charts.js is loaded)
    const salesChart = document.getElementById('salesChart');
    if (typeof Chart !== 'undefined' && salesChart) {
        new Chart(salesChart, {
            type: 'line',
            data: {
                labels: salesChartLabels, // This should be defined in the page
                datasets: [{
                    label: 'Sales',
                    data: salesChartData, // This should be defined in the page
                    backgroundColor: 'rgba(111, 78, 55, 0.2)',
                    borderColor: 'rgba(111, 78, 55, 1)',
                    borderWidth: 2,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Daily Sales'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });
    }

    const itemsChart = document.getElementById('itemsChart');
    if (typeof Chart !== 'undefined' && itemsChart) {
        new Chart(itemsChart, {
            type: 'doughnut',
            data: {
                labels: itemsChartLabels, // This should be defined in the page
                datasets: [{
                    data: itemsChartData, // This should be defined in the page
                    backgroundColor: [
                        'rgba(111, 78, 55, 0.8)',
                        'rgba(192, 160, 128, 0.8)',
                        'rgba(212, 167, 106, 0.8)',
                        'rgba(44, 30, 18, 0.8)',
                        'rgba(248, 243, 233, 0.8)',
                        'rgba(111, 78, 55, 0.5)',
                        'rgba(192, 160, 128, 0.5)',
                        'rgba(212, 167, 106, 0.5)'
                    ],
                    borderColor: 'white',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    title: {
                        display: true,
                        text: 'Popular Items'
                    }
                }
            }
        });
    }
}); 