(function() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.js-add-domain')) {
            e.preventDefault();

            let button = e.target.closest('.js-add-domain');
            let parent = button.closest('.domain').parentNode;
            var domainDiv = document.createElement('div');
            domainDiv.classList = 'domain';
            domainDiv.setAttribute('style', 'display:flex;align-items: center;');
            domainDiv.innerHTML = document.getElementById('js-domain-template').innerHTML;
            parent.appendChild(domainDiv);
            return false;
        }

        if (e.target.closest('.js-remove-domain')) {
            e.preventDefault();

            if (document.querySelectorAll('.domain').length === 1) return false;

            let button = e.target.closest('.js-remove-domain');
            let parent = button.closest('.domain');
            parent.remove();
            return false;
        }
    });

    document.addEventListener('submit', function(e) {
        if (e.target.classList.contains('okta-integration-options')) {
            e.preventDefault();

            let form = e.target.closest('.okta-integration-options');
            let force_auth = form.querySelector('#force_auth:checked') ? 1 : 0;
            let saml_login = form.querySelector('#saml_login').value;
            let domains = [];
            let domainInputs = form.querySelectorAll('[name="domains[]"]');

            for (let i = 0; i < domainInputs.length; i++) {
                domains.push(domainInputs[i].value);
            }

            let data = {
                force_auth: force_auth,
                saml_login: saml_login,
                domains: domains,
                submit: true
            };
            
            jQuery.ajax({
                type: 'POST',
                data: data,
                dataType: 'json'
            })
            .done(function(json) {
                if (typeof json.success !== 'undefined') {
                    alert('Okta options updated.');
                    return false;
                }

                console.log(json);
            })
            .fail(function(error) {
                console.log(error.responseText);
            });
            return false;
        }
    });
})();
