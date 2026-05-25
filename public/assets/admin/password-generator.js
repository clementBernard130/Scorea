(function () {
    'use strict';

    function getPwdField() {
        return document.getElementById('pwd-main-field')
            ?? document.querySelector('input[name="Users[password]"]');
    }

    function generatePassword() {
        const upper   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const lower   = 'abcdefghijklmnopqrstuvwxyz';
        const digits  = '0123456789';
        const special = '!@#$%^&*()-_=+[]{}|;:,.<>?';
        const all     = upper + lower + digits + special;
        const len     = 16;

        const arr = new Uint8Array(len);
        crypto.getRandomValues(arr);

        let pwd = [
            upper  [arr[0] % upper.length],
            lower  [arr[1] % lower.length],
            digits [arr[2] % digits.length],
            special[arr[3] % special.length],
        ];
        for (let i = 4; i < len; i++) {
            pwd.push(all[arr[i] % all.length]);
        }

        const shuffleArr = new Uint8Array(len);
        crypto.getRandomValues(shuffleArr);
        pwd.sort((a, b) => shuffleArr[pwd.indexOf(a)] - shuffleArr[pwd.indexOf(b)]);
        const result = pwd.join('');

        const field = getPwdField();
        if (field) {
            field.value = result;
            field.type  = 'password';
            document.getElementById('pwd-toggle-icon').className = 'fa fa-eye-slash';
        }

        analyzePassword(result);
    }

    function togglePwdVisibility() {
        const field = getPwdField();
        const icon  = document.getElementById('pwd-toggle-icon');
        if (!field) return;

        if (field.type === 'password') {
            field.type = 'text';
            icon.className = 'fa fa-eye-slash';
        } else {
            field.type = 'password';
            icon.className = 'fa fa-eye';
        }
    }

    function analyzePassword(pwd) {
        const hasUpper    = /[A-Z]/.test(pwd);
        const hasLower    = /[a-z]/.test(pwd);
        const hasDigit    = /[0-9]/.test(pwd);
        const hasSpec     = /[^A-Za-z0-9]/.test(pwd);
        const longEnough  = pwd.length >= 12;
        const pool        = (hasUpper ? 26 : 0) + (hasLower ? 26 : 0)
                          + (hasDigit ? 10 : 0) + (hasSpec  ? 30 : 0);
        const entropy     = pool > 0 ? Math.log2(Math.pow(pool, pwd.length)) : 0;
        const goodEntropy = entropy >= 80;

        setRule('r-len',     longEnough);
        setRule('r-upper',   hasUpper);
        setRule('r-lower',   hasLower);
        setRule('r-digit',   hasDigit);
        setRule('r-spec',    hasSpec);
        setRule('r-entropy', goodEntropy);

        const score  = [longEnough, hasUpper, hasLower, hasDigit, hasSpec, goodEntropy].filter(Boolean).length;
        const colors = ['#E24B4A', '#E24B4A', '#EF9F27', '#EF9F27', '#639922', '#1D9E75'];
        const labels = ['Très faible', 'Faible', 'Moyen', 'Acceptable', 'Fort', 'Très fort'];

        const bar   = document.getElementById('pwd-strength-bar');
        const label = document.getElementById('pwd-strength-label');
        bar.style.width      = Math.round(score / 6 * 100) + '%';
        bar.style.background = colors[score - 1] ?? '#ccc';
        label.textContent    = pwd.length ? 'Force : ' + (labels[score - 1] ?? '—') : '—';
        label.style.color    = colors[score - 1] ?? '#666';
    }

    function setRule(id, ok) {
        const el = document.getElementById(id);
        if (el) ok ? el.classList.add('ok') : el.classList.remove('ok');
    }

    function copyPassword() {
        const field = getPwdField();
        if (!field?.value) return;
        navigator.clipboard.writeText(field.value).then(() => {
            const msg = document.getElementById('pwd-copied-msg');
            msg.style.opacity = 1;
            setTimeout(() => msg.style.opacity = 0, 2000);
        });
    }

    // Exposition globale pour les onclick="" du Twig
    window.generatePassword     = generatePassword;
    window.togglePwdVisibility  = togglePwdVisibility;
    window.copyPassword         = copyPassword;

    // Analyse en temps réel si l'admin tape manuellement
    document.addEventListener('DOMContentLoaded', () => {
        const field = getPwdField();
        if (field) {
            field.addEventListener('input', () => analyzePassword(field.value));
        }
    });
}());