document.addEventListener("DOMContentLoaded", function() {
    function updateCountdown() {
        var countdownElements = document.querySelectorAll('[id^="countdown-"]');

        countdownElements.forEach(function(element) {
            var endTime = new Date(parseInt(element.getAttribute('data-upgrade-end-time')) * 1000);
            var now = new Date();
            var timeLeft = endTime - now;

            if (timeLeft > 0) {
                var days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                var hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

                var countdownText = '';

                if (days > 0) {
                    countdownText += days + " jours ";
                }
                if (hours > 0) {
                    countdownText += hours + " heures ";
                }
                if (minutes > 0) {
                    countdownText += minutes + " minutes ";
                }
                if (seconds > 0 || countdownText === '') {
                    countdownText += seconds + " secondes ";
                }

                element.textContent = countdownText;
            } else {
                if (window.customTextContent) {
                    element.textContent = window.customTextContent;
                } else {
                    element.textContent = 'Mise à niveau terminée!';
                }
            }
        });
    }

    // Exécutez la fonction updateCountdown immédiatement
    updateCountdown();

    // Ensuite, configurez-la pour s'exécuter toutes les secondes
    setInterval(updateCountdown, 1000);
});
