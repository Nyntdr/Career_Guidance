// Lightweight, dependency-free client-side validation for Inertia `useForm`.
//
// Usage:
//   import useFormValidation, { validation as rules } from '@/composables/useFormValidation';
//
//   const { validate } = useFormValidation();
//   const submit = () => {
//       if (!validate(form, {
//           email: [rules.required(), rules.email()],
//           password: [rules.required(), rules.min(8)],
//       })) return;
//       form.post(route('login'));
//   };
//
// Rules are factories that return a validator `(value, form) => message | null`.
// Errors are written via `form.setError(field, message)` so the existing
// `<InputError :message="form.errors.field" />` slots render them instantly.

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

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
            if (isEmpty(value)) return null; // required() covers the empty case
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

    // Must be on or after the value of another field (e.g. end_date >= start_date).
    afterOrEqualField(other, message = 'Must be on or after the start date.') {
        return (value, form) => {
            if (isEmpty(value) || isEmpty(form[other])) return null;
            return new Date(value) < new Date(form[other]) ? message : null;
        };
    },

    // Numeric: this field's value must be >= another field's value (e.g. salary_max >= salary_min).
    gteField(other, message = 'Must be at least the minimum value.') {
        return (value, form) => {
            if (isEmpty(value) || isEmpty(form[other])) return null;
            return Number(value) < Number(form[other]) ? message : null;
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

    // Requires at least one of this field OR another field to be filled.
    eitherOr(other, message = 'Please provide at least one of these fields.') {
        return (value, form) =>
            isEmpty(value) && isEmpty(form[other]) ? message : null;
    },
};

export function useFormValidation() {
    function validate(form, rules) {
        form.clearErrors();

        let valid = true;

        for (const [field, fieldRules] of Object.entries(rules)) {
            const validators = Array.isArray(fieldRules) ? fieldRules : [fieldRules];

            for (const validator of validators) {
                const message = validator(form[field], form);
                if (message) {
                    form.setError(field, message);
                    valid = false;
                    break;
                }
            }
        }

        return valid;
    }

    return { validate };
}

export default useFormValidation;