// eslint.config.js
import vue from "eslint-plugin-vue";

export default [
    {
        files: ["*.js", "*.vue"],
        languageOptions: {
            parserOptions: {
                ecmaVersion: 2020,
                sourceType: "module"
            }
        },
        plugins: {
            vue
        },
        rules: {
            "vue/no-unused-vars": "error",
            "prettier/prettier": "error"
            // здесь остальные правила
        },
        extends: [
            "plugin:vue/vue3-recommended",
            "eslint:recommended",
            "plugin:prettier/recommended"
        ]
    }
];
