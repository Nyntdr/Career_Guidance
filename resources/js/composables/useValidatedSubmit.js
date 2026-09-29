import { useFormValidation } from '@/composables/useFormValidation';

export function useValidatedSubmit() {
    const { validate, focusFirstError } = useFormValidation();

    function submit(form, rulesMap, method, url, options = {}) {
        if (rulesMap && !validate(form, rulesMap)) {
            focusFirstError(form);
            return false;
        }

        const originalOnError = options.onError;

        const mergedOptions = {
            ...options,
            onError: (errors) => {
                if (typeof originalOnError === 'function') {
                    originalOnError(errors);
                }
                focusFirstError(form);
            },
        };

        if (typeof form[method] === 'function') {
            form[method](url, mergedOptions);
        }

        return true;
    }

    return { submit };
}

export default useValidatedSubmit;
