// Lightweight, dependency-free client-side validation for Inertia `useForm`.

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const ALPHA_SPACES_RE = /^[a-zA-Z\s]+$/;

function isEmpty(value) {
    return (
        value === null ||
        value === undefined ||
        value === '' ||
        (Array.isArray(value) && value.length === 0)
    );
}

export const validation = {
    required(message = 'This field is required.') {
        return (value) => (isEmpty(value) ? message : null);
    },

    alphaSpaces(message = 'This field may only contain letters and spaces.') {
        return (value) =>
            isEmpty(value) || ALPHA_SPACES_RE.test(String(value)) ? null : message;
    },

    email(message = 'Please enter a valid email address.') {
        return (value) =>
            isEmpty(value) || EMAIL_RE.test(String(value).trim()) ? null : message;
    },

    min(len, message = `Must be at least ${len} characters.`) {
        return (value) =>
            !isEmpty(value) && String(value).trim().length < len ? message : null;
    },

    max(len, message = `Must not exceed ${len} characters.`) {
        return (value) =>
            !isEmpty(value) && String(value).length > len ? message : null;
    },

    confirmed(confirmation, message = 'The confirmation does not match.') {
        return (value, form) => {
            if (isEmpty(value)) return null;
            return value !== form[confirmation] ? message : null;
        };
    },

    integer(message = 'Please enter a whole number.') {
        return (value) => {
            if (isEmpty(value)) return null;
            const n = Number(value);
            return Number.isInteger(n) ? null : message;
        };
    },

    numeric(message = 'Please enter a valid number.') {
        return (value) =>
            isEmpty(value) || !Number.isNaN(Number(value)) ? null : message;
    },

    minValue(min, message = `Must be at least ${min}.`) {
        return (value) =>
            isEmpty(value) || Number.isNaN(Number(value)) || Number(value) >= min
                ? null
                : message;
    },

    maxValue(max, message = `Must not exceed ${max}.`) {
        return (value) =>
            isEmpty(value) || Number.isNaN(Number(value)) || Number(value) <= max
                ? null
                : message;
    },

    date(message = 'Please enter a valid date.') {
        return (value) =>
            isEmpty(value) || !Number.isNaN(Date.parse(value)) ? null : message;
    },

    afterOrEqualField(other, message = 'Must be on or after the start date.') {
        return (value, form) => {
            if (isEmpty(value) || isEmpty(form[other])) return null;
            return new Date(value) < new Date(form[other]) ? message : null;
        };
    },

    url(message = 'Please enter a valid URL.') {
        return (value) => {
            if (isEmpty(value)) return null;
            try {
                new URL(value);
                return null;
            } catch {
                return message;
            }
        };
    },

    oneOf(options, message = 'Please select a valid option.') {
        return (value) =>
            isEmpty(value) || options.includes(value) ? null : message;
    },

    eitherOr(other, message = 'Please provide at least one of these fields.') {
        return (value, form) =>
            isEmpty(value) && isEmpty(form[other]) ? message : null;
    },

    file(opts = {}, message = null) {
        const { extensions = [], maxBytes = null, mimeTypes = [] } = opts;
        return (value) => {
            if (isEmpty(value) || !(value instanceof File)) return null;

            if (maxBytes && value.size > maxBytes) {
                const maxMb = (maxBytes / (1024 * 1024)).toFixed(1);
                return message || `File size must not exceed ${maxMb} MB.`;
            }

            if (extensions.length > 0) {
                const ext = value.name.split('.').pop()?.toLowerCase();
                if (!extensions.includes(ext)) {
                    return message || `File extension must be one of: ${extensions.join(', ')}.`;
                }
            }

            if (mimeTypes.length > 0 && value.type) {
                if (!mimeTypes.includes(value.type)) {
                    return message || `Invalid file MIME type: ${value.type}.`;
                }
            }

            return null;
        };
    },

    minFileBytes(minBytes, message = null) {
        return (value) => {
            if (isEmpty(value) || !(value instanceof File)) return null;
            if (value.size < minBytes) {
                return message || `File size must be at least ${minBytes} bytes.`;
            }
            return null;
        };
    },
};

export function useFormValidation() {
    function validateField(form, field, fieldRules) {
        if (!fieldRules) return null;
        const validators = Array.isArray(fieldRules) ? fieldRules : [fieldRules];

        for (const validator of validators) {
            const message = validator(form[field], form);
            if (message) {
                form.setError(field, message);
                return message;
            }
        }

        if (form.errors[field]) {
            form.clearErrors(field);
        }

        return null;
    }

    function validate(form, rulesMap) {
        form.clearErrors();
        let valid = true;

        for (const [field, fieldRules] of Object.entries(rulesMap)) {
            const msg = validateField(form, field, fieldRules);
            if (msg) {
                valid = false;
            }
        }

        return valid;
    }

    function attach(form, rulesMap) {
        function resolveFieldName(target) {
            if (!target) return null;
            return target.getAttribute('name') || target.getAttribute('id') || target.dataset.field;
        }

        function onBlur(e) {
            const field = resolveFieldName(e.target);
            if (field && rulesMap[field]) {
                validateField(form, field, rulesMap[field]);
            }
        }

        function onInput(e) {
            const field = resolveFieldName(e.target);
            if (field && rulesMap[field] && form.errors[field]) {
                validateField(form, field, rulesMap[field]);
            }
        }

        return { onBlur, onInput };
    }

    function focusFirstError(form, container = document) {
        const errorFields = Object.keys(form.errors || {});
        if (errorFields.length === 0) return;

        const firstField = errorFields[0];
        const el = container.querySelector(
            `[data-error="${firstField}"], [name="${firstField}"], #${firstField}`
        );

        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (typeof el.focus === 'function') {
                el.focus();
            }
        }
    }

    function errorSummary(form) {
        const entries = Object.entries(form.errors || {});
        return {
            count: entries.length,
            errors: entries.map(([field, message]) => ({ field, message })),
        };
    }

    return {
        validate,
        validateField,
        attach,
        focusFirstError,
        errorSummary,
    };
}

export default useFormValidation;