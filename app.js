

document.addEventListener('DOMContentLoaded', () => {
    const voteButtons = document.querySelectorAll('.vote-button');

    voteButtons.forEach(button => {
        button.addEventListener('click', async (event) => {
            // Evitem que el formulari recarregui la pàgina
            event.preventDefault();

            const form = button.closest('form');
            if (!form) return;

            const row = form.closest('tr');
            const bitzId = row?.getAttribute('data-bitz-id');
            const vote = button.getAttribute('data-vote');

            if (!bitzId || !vote) return;

            const formData = new URLSearchParams();
            formData.append('bitz_id', bitzId);
            formData.append('vote', vote);

            try {
                const response = await fetch('vote.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData.toString()
                });

                const data = await response.json();

                if (data.success) {
                    // Actualitzem el rànquing a la fila
                    const rankingCell = row.querySelector('.ranking');
                    if (rankingCell) {
                        rankingCell.textContent = data.ranking;
                        location.reload();
                    }
                }
            } catch (error) {
                console.error('Error enviant el vot', error);
                // En cas d'error, fem submit normal del formulari
                form.submit();
            }
        });
    });
});

