document.addEventListener('DOMContentLoaded', () => {
    const rootElement = document.getElementById('tile-mystique-root');
    if (!rootElement) {
        console.error("Le point de montage #tile-mystique-root est introuvable !");
        return;
    }

    const TileMystique = () => {
        const [tiles, setTiles] = React.useState([]);
        const [score, setScore] = React.useState(0);

        React.useEffect(() => {
            const shuffledTiles = createShuffledTiles();
            setTiles(shuffledTiles);
        }, []);

        const createShuffledTiles = () => {
            const positions = [];
            for (let row = 0; row < 4; row++) {
                for (let col = 0; col < 4; col++) {
                    positions.push({ row, col });
                }
            }
            positions.sort(() => Math.random() - 0.5); // Mélanger les tuiles
            return positions;
        };

        const handleTileClick = (index) => {
            // Logique pour déplacer une tuile
            // Ajouter votre logique pour déterminer si le déplacement est valide
            const emptyIndex = tiles.findIndex((tile) => !tile); // Tuile vide
            if (isMoveValid(index, emptyIndex)) {
                const newTiles = [...tiles];
                [newTiles[index], newTiles[emptyIndex]] = [newTiles[emptyIndex], newTiles[index]];
                setTiles(newTiles);
                setScore(score + 1);
            }
        };

        const isMoveValid = (index, emptyIndex) => {
            // Vérifie si la tuile peut être déplacée (en fonction des règles du puzzle)
            const diff = Math.abs(index - emptyIndex);
            return diff === 1 || diff === 4; // Adjacence
        };

        return React.createElement(
            'div',
            { className: 'puzzle-mystique' },
            React.createElement('h1', null, 'Tuile Mystique'),
            React.createElement(
                'div',
                { className: 'puzzle-container' },
                tiles.map((tile, index) =>
                    React.createElement(
                        'div',
                        {
                            key: index,
                            className: tile ? 'puzzle-tile' : 'puzzle-tile empty-tile',
                            style: tile
                                ? {
                                      backgroundPosition: `-${tile.col * 100}px -${tile.row * 100}px`,
                                  }
                                : {},
                            onClick: () => handleTileClick(index),
                        },
                        null
                    )
                )
            ),
            React.createElement('p', null, `Score actuel : ${score}`)
        );
    };

    const root = ReactDOM.createRoot(rootElement);
    root.render(React.createElement(TileMystique));
});
