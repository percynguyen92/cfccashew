// resources/js/utils/validation.js
class FormValidator {
    constructor() {
        this.errors = {};
    }

    validate(data, rules) {
        this.errors = {};

        for (const field in rules) {
            const value = data[field];
            const fieldRules = rules[field];

            for (const rule of fieldRules) {
                if (!this.runRule(value, rule, field)) {
                    break; // Stop at first error for this field
                }
            }
        }

        return Object.keys(this.errors).length === 0;
    }

    runRule(value, rule, field) {
        const [ruleName, ...params] = rule.split(":");

        switch (ruleName) {
            case "required":
                if (!value || value.toString().trim() === "") {
                    this.addError(
                        field,
                        `${this.getFieldName(field)} là bắt buộc`
                    );
                    return false;
                }
                break;

            case "min":
                if (value && parseFloat(value) < parseFloat(params[0])) {
                    this.addError(
                        field,
                        `${this.getFieldName(field)} phải lớn hơn hoặc bằng ${
                            params[0]
                        }`
                    );
                    return false;
                }
                break;

            case "max":
                if (value && parseFloat(value) > parseFloat(params[0])) {
                    this.addError(
                        field,
                        `${this.getFieldName(field)} phải nhỏ hơn hoặc bằng ${
                            params[0]
                        }`
                    );
                    return false;
                }
                break;

            case "numeric":
                if (value && isNaN(parseFloat(value))) {
                    this.addError(
                        field,
                        `${this.getFieldName(field)} phải là số`
                    );
                    return false;
                }
                break;
        }

        return true;
    }

    addError(field, message) {
        if (!this.errors[field]) {
            this.errors[field] = [];
        }
        this.errors[field].push(message);
    }

    getFieldName(field) {
        const fieldNames = {
            billNumber: "Số Bill",
            seller: "Người bán",
            buyer: "Người mua",
            sample_weight: "Trọng lượng mẫu",
            w_good_kernel: "Trọng lượng nhân tốt",
        };

        return fieldNames[field] || field;
    }

    getErrors() {
        return this.errors;
    }

    hasErrors() {
        return Object.keys(this.errors).length > 0;
    }
}

// Make it globally available
window.FormValidator = FormValidator;
