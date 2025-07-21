const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

// Multiple entry points für verschiedene Bundles
const entryPoints = {
    // Admin Interface
    'admin': path.resolve(process.cwd(), 'src/admin/index.tsx'),
    
    // Gutenberg Blocks
    'blocks': path.resolve(process.cwd(), 'src/blocks/index.ts'),
    
    // Container Block
    'blocks/container/index': path.resolve(process.cwd(), 'src/blocks/container/index.tsx'),
    
    // Frontend Scripts (falls benötigt)
    'frontend': path.resolve(process.cwd(), 'src/frontend/index.ts'),
};

module.exports = {
    ...defaultConfig,
    
    entry: entryPoints,
    
    output: {
        ...defaultConfig.output,
        path: path.resolve(process.cwd(), 'build'),
        filename: '[name].js',
    },
    
    resolve: {
        ...defaultConfig.resolve,
        extensions: ['.ts', '.tsx', '.js', '.jsx', '.json'],
        alias: {
            '@': path.resolve(process.cwd(), 'src'),
            '@components': path.resolve(process.cwd(), 'src/components'),
            '@utils': path.resolve(process.cwd(), 'src/utils'),
            '@blocks': path.resolve(process.cwd(), 'src/blocks'),
            '@admin': path.resolve(process.cwd(), 'src/admin'),
            '@types': path.resolve(process.cwd(), 'src/types'),
        },
    },
    
    module: {
        ...defaultConfig.module,
        rules: [
            // TypeScript/TSX handling
            {
                test: /\.(ts|tsx)$/,
                exclude: /node_modules/,
                use: [
                    {
                        loader: require.resolve('ts-loader'),
                        options: {
                            transpileOnly: true,
                        },
                    },
                ],
            },
            
            // Andere Regeln von @wordpress/scripts beibehalten
            ...defaultConfig.module.rules.filter(
                rule => !rule.test || !rule.test.toString().includes('tsx')
            ),
        ],
    },
    
    externals: {
        ...defaultConfig.externals,
        react: 'React',
        'react-dom': 'ReactDOM',
        lodash: 'lodash',
    },
    
    optimization: {
        ...defaultConfig.optimization,
        splitChunks: {
            cacheGroups: {
                style: {
                    test: /\.(sc|sa|c)ss$/,
                    chunks: 'all',
                    enforce: true,
                    name(module, chunks, cacheGroupKey) {
                        const chunkName = chunks[0].name;
                        if (chunkName.includes('blocks/')) {
                            // Separate styles für individual blocks
                            return chunkName.replace('index', 'style');
                        }
                        // Andere styles nach entry point
                        return `${cacheGroupKey}-${chunkName}`;
                    },
                },
            },
        },
    },
};