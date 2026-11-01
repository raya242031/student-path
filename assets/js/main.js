   const form = document.querySelector(
        'form[action="add_student.php"]'
    );

    if (!form) {
        return;
    }

    const message = document.getElementById('formMessage');
    const name = form.querySelector('[name="name"]');
    const email = form.querySelector('[name="email"]');
    const phone = form.querySelector('[name="phone"]');
    const submitButton = form.querySelector('[type="submit"]');

    function showMessage(text, type) {
        message.textContent = text;
        message.className = 'alert alert-' + type + ' mb-3';
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        if (!/^[0-9]{1,15}$/.test(phone.value)) {
            showMessage(
                'رقم الهاتف يجب أن يحتوي على أرقام فقط وبحد أقصى 15 رقمًا.',
                'danger'
            );
            phone.focus();
            return;
        }

        const originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.textContent = 'جارٍ الحفظ...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                showMessage(result.message, 'success');

                if (result.redirect) {
                    window.location.href = result.redirect;
                }
            } else {
                showMessage(
                    result.message || 'تعذر حفظ البيانات.',
                    'danger'
                );
            }
        } catch (error) {
            showMessage(
                'حدث خطأ في الاتصال بالخادم أو في استجابة البيانات.',
                'danger'
            );
            console.error(error);
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    });