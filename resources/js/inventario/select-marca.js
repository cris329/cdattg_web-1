/**
 * select-marca.js - Autocompletado del campo Marca en crear producto
 */

document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('marca_nombre');
    const hiddenId = document.getElementById('marca_id');

    if (!input || !hiddenId) {
        return;
    }

    let marcas = [];
    try {
        const raw = input.dataset.marcas ?? '[]';
        marcas = JSON.parse(raw);
    } catch (error) {
        console.error('select-marca: error al parsear data-marcas', error);
        marcas = [];
    }

    if (!Array.isArray(marcas) || marcas.length === 0) {
        return;
    }

    const container = document.createElement('div');
    container.className = 'position-relative';
    input.parentNode.insertBefore(container, input);
    container.appendChild(input);

    const list = document.createElement('div');
    list.className = 'brand-suggestions';
    list.style.position = 'absolute';
    list.style.top = '100%';
    list.style.left = '0';
    list.style.right = '0';
    list.style.zIndex = '999';
    list.style.background = '#ffffff';
    list.style.border = '1px solid #e2e8f0';
    list.style.borderTop = 'none';
    list.style.maxHeight = '200px';
    list.style.overflowY = 'auto';
    list.style.display = 'none';
    container.appendChild(list);

    function renderSuggestions(term, showAllWhenEmpty) {
        const value = term.toLowerCase().trim();
        list.innerHTML = '';

        let matches = [];
        if (!value && showAllWhenEmpty) {
            matches = marcas.slice(0, 10);
        } else if (value) {
            matches = marcas
                .filter(marca => (marca?.name ?? '').toLowerCase().includes(value))
                .slice(0, 10);
        }

        if (matches.length === 0) {
            list.style.display = 'none';
            return;
        }

        for (const marca of matches) {
            const item = document.createElement('button');
            item.type = 'button';
            item.textContent = marca.name;
            item.style.display = 'block';
            item.style.width = '100%';
            item.style.textAlign = 'left';
            item.style.padding = '8px 12px';
            item.style.border = 'none';
            item.style.background = 'white';
            item.style.cursor = 'pointer';

            item.addEventListener('mouseover', function() {
                this.style.background = '#f7fafc';
            });
            item.addEventListener('mouseout', function() {
                this.style.background = 'white';
            });

            item.addEventListener('click', function() {
                input.value = marca.name;
                hiddenId.value = String(marca.id);
                list.style.display = 'none';
            });

            list.appendChild(item);
        }

        list.style.display = 'block';
    }

    input.addEventListener('input', function() {
        hiddenId.value = '';
        renderSuggestions(this.value, false);
    });

    input.addEventListener('focus', function() {
        renderSuggestions(this.value, true);
    });

    input.addEventListener('blur', function() {
        setTimeout(() => {
            list.style.display = 'none';
        }, 150);
    });

    const initialId = hiddenId.value;
    if (initialId) {
        const match = marcas.find(marca => String(marca.id) === String(initialId));
        if (match) {
            input.value = match.name;
        }
    }
});


