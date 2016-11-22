module.exports = {
    "rules": {
        "camelcase": 0,
        "brace-style": [2, "1tbs"],
        "quotes": [0, "single"],
        "semi": [2, "always"],
        "space-infix-ops": 2,
    },
    "env": {
        "es6": true,
        "node": true,
        "browser": true,
        "jquery": true
    },
    "parser": "babel-eslint",
    "ecmaFeatures": {
        "jsx": true
    },
    "plugins": [
        "react"
    ]
};
