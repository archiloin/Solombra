document.addEventListener('DOMContentLoaded', () => {
    const rootElement = document.getElementById('shadow-fight-root');
    if (!rootElement) {
        console.error("Le point de montage #shadow-fight-root est introuvable !");
        return;
    }

    const gameData = JSON.parse(rootElement.getAttribute('data-game-data'));

    function ShadowFight() {
        const [heroHealth, setHeroHealth] = React.useState(gameData.player.health);
        const [enemyHealth, setEnemyHealth] = React.useState(gameData.enemy.health);
        const [logs, setLogs] = React.useState([]);
        const [isGameOver, setIsGameOver] = React.useState(false);

        const heroRef = React.useRef(null);
        const enemyRef = React.useRef(null);

        const animateAttack = () => {
            const tl = gsap.timeline();
            if (heroRef.current && enemyRef.current) {
                tl.to(heroRef.current, { x: -30, duration: 0.2 })
                    .to(heroRef.current, { x: 0, duration: 0.2 })
                    .to(enemyRef.current, { x: 30, duration: 0.2 })
                    .to(enemyRef.current, { x: 0, duration: 0.2 });
            }
        };

        const addLog = (message) => {
            setLogs((prevLogs) => [...prevLogs, message]);
        };

        const updateHeroHealthInDB = (heroHealth, enemyHealth, enemyXp) => {
            fetch('/api/shadow-fight/update-health', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ heroHealth, enemyHealth, enemyXp }),
            }).then((response) => {
                if (!response.ok) {
                    console.error("Erreur lors de la mise à jour des données du héros.");
                }
            });
        };

        const handleAttack = () => {
            if (isGameOver) return;

            animateAttack();

            const heroDamage = Math.floor(Math.random() * 20) + 5;
            const enemyDamage = Math.floor(Math.random() * 20) + 5;

            const newHeroHealth = Math.max(heroHealth - enemyDamage, 0);
            const newEnemyHealth = Math.max(enemyHealth - heroDamage, 0);

            setHeroHealth(newHeroHealth);
            setEnemyHealth(newEnemyHealth);

            addLog(`Vous infligez ${heroDamage} dégâts à l'ennemi.`);
            addLog(`L'ennemi inflige ${enemyDamage} dégâts à vous.`);

            if (newEnemyHealth <= 0) {
                setIsGameOver(true);
                addLog(`Victoire ! Vous avez vaincu l'ennemi et remporté ${gameData.enemy.xp} XP !`);
                updateHeroHealthInDB(newHeroHealth, newEnemyHealth, gameData.enemy.xp);
            } else {
                updateHeroHealthInDB(newHeroHealth, newEnemyHealth, 0);
            }
        };

        const handleNextLevel = () => {
            window.location.reload(); // Recharge la page pour afficher le prochain niveau
        };

        React.useEffect(() => {
            if (heroHealth <= 0) {
                setIsGameOver(true);
                addLog("Vous avez perdu ! L'ennemi vous a vaincu.");
            }
        }, [heroHealth, enemyHealth]);

        return React.createElement(
            'div',
            { className: 'shadow-fight' },
            React.createElement('h1', { className: 'text-center mb-4' }, 'Combat des Ombres'),
            React.createElement(
                'div',
                { className: 'fight-area' },
                React.createElement(
                    'div',
                    { className: 'character', ref: heroRef },
                    React.createElement('h2', null, gameData.player.name),
                    React.createElement('p', null, `Santé : ${heroHealth}`)
                ),
                React.createElement(
                    'div',
                    { className: 'character', ref: enemyRef },
                    React.createElement('h2', null, gameData.enemy.name),
                    React.createElement('p', null, `Santé : ${enemyHealth}`)
                )
            ),
            isGameOver && heroHealth > 0
                ? React.createElement(
                      'button',
                      { onClick: handleNextLevel, className: 'attack-button' },
                      'Suivant'
                  )
                : React.createElement(
                      'button',
                      { onClick: handleAttack, disabled: isGameOver, className: 'attack-button' },
                      'Attaquer'
                  ),
            React.createElement(
                'div',
                { className: 'log-area' },
                React.createElement('h3', null, 'Journal de Combat'),
                React.createElement(
                    'ul',
                    null,
                    logs.map((log, index) => React.createElement('li', { key: index }, log))
                )
            ),
            isGameOver &&
                React.createElement(
                    'div',
                    { className: 'game-over' },
                    heroHealth > 0 ? 'Victoire !' : 'Défaite...'
                )
        );
    }

    const root = ReactDOM.createRoot(rootElement);
    root.render(React.createElement(ShadowFight));
});
