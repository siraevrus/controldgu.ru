import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

/** Маска +7 xxx-xxx-xx-xx; в форму уходит +7XXXXXXXXXX при 10 цифрах после кода. */
Alpine.directive('phone-mask', (el) => {
    const digits = (s) => s.replace(/\D/g, '');

    const toBody10 = (s) => {
        let d = digits(s);
        if (d.length === 0) {
            return '';
        }
        if (d[0] === '7') {
            d = d.slice(1);
        } else if (d[0] === '8') {
            d = d.slice(1);
        }
        return d.slice(0, 10);
    };

    const formatDisplay = (s) => {
        const body = toBody10(s);
        if (body.length === 0) {
            return '';
        }
        let out = '+7 ';
        const a = body.slice(0, 3);
        const b = body.slice(3, 6);
        const c = body.slice(6, 8);
        const d = body.slice(8, 10);
        out += a;
        if (b.length) {
            out += `-${b}`;
        }
        if (c.length) {
            out += `-${c}`;
        }
        if (d.length) {
            out += `-${d}`;
        }
        return out;
    };

    const normalizeSubmit = (s) => {
        const body = toBody10(s);
        if (body.length !== 10) {
            return s.trim();
        }
        return `+7${body}`;
    };

    const applyFormat = () => {
        const next = formatDisplay(el.value);
        if (next !== el.value) {
            el.value = next;
        }
        const len = el.value.length;
        el.setSelectionRange(len, len);
    };

    if (el.value) {
        el.value = formatDisplay(el.value);
    }

    el.addEventListener('input', applyFormat);

    el.addEventListener('focus', () => {
        if (el.value === '' || el.value === '+7' || el.value === '+7 ') {
            el.value = '+7 ';
            el.setSelectionRange(4, 4);
        }
    });

    el.addEventListener('blur', () => {
        if (toBody10(el.value).length === 0) {
            el.value = '';
        }
    });

    const form = el.closest('form');
    if (form) {
        form.addEventListener('submit', () => {
            const body = toBody10(el.value);
            if (body.length === 10) {
                el.value = normalizeSubmit(el.value);
            }
        });
    }
});

Alpine.start();
