(function () {
    'use strict';

    var roots = [];
    var state = null;
    var selectedPoints = 0;
    var refreshTimer = null;
    var endpoint = '';
    var userId = 0;
    var storageKey = 'dnepritloyalty_points';

    function number(value) {
        value = parseFloat(value);
        return isFinite(value) ? value : 0;
    }

    function round(value) {
        return Math.round((number(value) + Number.EPSILON) * 100) / 100;
    }

    function money(value) {
        try {
            return new Intl.NumberFormat('uk-UA', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            }).format(round(value));
        } catch (e) {
            return String(round(value));
        }
    }

    function text(root, role, value) {
        var el = root.querySelector('[data-loyalty-role="' + role + '"]');
        if (el) {
            el.textContent = value;
        }
    }

    function setMoneyElement(el, value) {
        if (!el || el.closest('[data-dneprit-loyalty-cart]')) {
            return;
        }

        var formatted = money(value);

        if (el.tagName === 'INPUT') {
            el.value = round(value).toFixed(2);
            return;
        }

        var nested = el.querySelector(
            '[data-ms2-price-value], [data-price-value], .price-value, .amount-value'
        );

        if (nested) {
            nested.textContent = formatted;
        } else if (!el.children.length) {
            el.textContent = formatted;
        } else {
            var textNodes = Array.prototype.filter.call(el.childNodes, function (node) {
                return node.nodeType === 3 && /[0-9]/.test(node.nodeValue || '');
            });

            if (textNodes.length) {
                textNodes[0].nodeValue = formatted;
            }
        }

        el.setAttribute('data-dneprit-loyalty-adjusted', '1');
    }

    function syncMiniShopTotals(finalCost) {
        var selectors = [
            '.ms2_total_cost',
            '.ms2_order_cost',
            '[data-ms2-total-cost]',
            '[data-ms2-order-cost]'
        ];
        var seen = [];

        selectors.forEach(function (selector) {
            Array.prototype.forEach.call(document.querySelectorAll(selector), function (el) {
                if (seen.indexOf(el) !== -1) {
                    return;
                }
                seen.push(el);
                setMoneyElement(el, finalCost);
            });
        });

        try {
            document.dispatchEvent(new CustomEvent('dnepritloyalty:totalChanged', {
                detail: {
                    cart_cost: state ? round(state.cart_cost) : 0,
                    discount_amount: state ? round(state.discount_amount) : 0,
                    points: round(selectedPoints),
                    final_cost: round(finalCost)
                }
            }));
        } catch (e) {}
    }

    function show(el, visible) {
        if (!el) {
            return;
        }
        el.hidden = !visible;
    }

    function getOrderForms() {
        var forms = Array.prototype.slice.call(document.querySelectorAll('form'));

        return forms.filter(function (form) {
            if (form.id === 'msOrder') {
                return true;
            }

            if (form.classList.contains('ms2_order_form')) {
                return true;
            }

            if (form.querySelector('[name="ms2_action"][value="order/submit"]')) {
                return true;
            }

            if (form.querySelector('[name="action"][value="order/submit"]')) {
                return true;
            }

            return !!(
                form.querySelector('[name="delivery"]') &&
                form.querySelector('[name="payment"]')
            );
        });
    }

    function syncOrderForms() {
        getOrderForms().forEach(function (form) {
            var input = form.querySelector('input[data-dneprit-loyalty-order-input]');

            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'dneprit_loyalty_points';
                input.setAttribute('data-dneprit-loyalty-order-input', '1');
                form.appendChild(input);
            }

            input.value = selectedPoints.toFixed(2);
        });
    }

    function storeSelected() {
        try {
            window.sessionStorage.setItem(storageKey, selectedPoints.toFixed(2));
        } catch (e) {}
    }

    function loadSelected() {
        try {
            return number(window.sessionStorage.getItem(storageKey));
        } catch (e) {
            return 0;
        }
    }

    function clampSelected(value) {
        var max = state ? number(state.max_points) : 0;
        return round(Math.max(0, Math.min(number(value), max)));
    }

    function render() {
        if (!state) {
            return;
        }

        selectedPoints = clampSelected(selectedPoints);
        storeSelected();
        syncOrderForms();

        var pointValue = Math.max(0.000001, number(state.point_value) || 1);
        var spendMoney = Math.min(number(state.after_discount), round(selectedPoints * pointValue));
        var finalCost = Math.max(0, round(number(state.after_discount) - spendMoney));
        var canSpend = !!state.can_spend && number(state.max_points) > 0;

        syncMiniShopTotals(finalCost);

        roots.forEach(function (root) {
            root.classList.toggle('is-disabled', !canSpend);

            text(root, 'available', money(state.available));
            text(root, 'cart-cost', money(state.cart_cost));
            text(root, 'discount', money(state.discount_amount));
            text(root, 'after-discount', money(state.after_discount));
            text(root, 'selected-money', money(spendMoney));
            text(root, 'final-cost', money(finalCost));
            text(root, 'max-points', money(state.max_points));
            text(root, 'max-percent', money(state.max_spend_percent));

            var level = root.querySelector('[data-loyalty-role="level"]');
            if (level) {
                var title = state.level_title || '';
                var discountLabel = '';
                if (number(state.discount_value) > 0) {
                    discountLabel = state.discount_type === 'fixed'
                        ? money(state.discount_value) + ' грн'
                        : money(state.discount_value) + '%';
                }

                if (title && discountLabel) {
                    level.textContent = title + ' · ' + discountLabel;
                } else if (title) {
                    level.textContent = title;
                } else if (discountLabel) {
                    level.textContent = discountLabel;
                } else {
                    level.textContent = root.getAttribute('data-label-base') || 'Бонусна програма';
                }
            }

            var range = root.querySelector('[data-loyalty-input="range"]');
            var input = root.querySelector('[data-loyalty-input="number"]');
            var spendWrap = root.querySelector('[data-loyalty-role="spend-wrap"]');
            var empty = root.querySelector('[data-loyalty-role="empty"]');
            var summary = root.querySelector('[data-loyalty-role="summary"]');
            var discountRow = root.querySelector('[data-loyalty-role="discount-row"]');
            var bonusRow = root.querySelector('[data-loyalty-role="bonus-row"]');
            var notice = root.querySelector('[data-loyalty-role="notice"]');

            if (range) {
                range.max = String(state.max_points || 0);
                range.value = String(selectedPoints);
                range.disabled = !canSpend;
            }

            if (input) {
                input.max = String(state.max_points || 0);
                input.value = selectedPoints ? selectedPoints.toFixed(2).replace(/\.00$/, '') : '0';
                input.disabled = !canSpend;
                input.removeAttribute('name');
            }

            show(empty, number(state.cart_cost) <= 0);
            show(summary, number(state.cart_cost) > 0);
            show(spendWrap, number(state.cart_cost) > 0);
            show(discountRow, number(state.discount_amount) > 0);
            show(bonusRow, selectedPoints > 0);

            if (notice) {
                var message = '';
                if (!state.spend_enabled) {
                    message = root.getAttribute('data-msg-disabled') || '';
                } else if (number(state.cart_cost) <= 0) {
                    message = root.getAttribute('data-msg-empty') || '';
                } else if (number(state.available) < number(state.min_spend_points)) {
                    message = (root.getAttribute('data-msg-min-points') || '') + ' ' + money(state.min_spend_points);
                } else if (number(state.after_discount) < number(state.min_order_for_spend)) {
                    message = (root.getAttribute('data-msg-min-order') || '') + ' ' + money(state.min_order_for_spend) + ' грн';
                } else if (!canSpend) {
                    message = root.getAttribute('data-msg-unavailable') || '';
                } else {
                    message = (root.getAttribute('data-msg-max') || '') + ' ' + money(state.max_points) + ' ' + (root.getAttribute('data-points-word') || 'бонусів');
                }
                notice.textContent = message;
            }
        });
    }

    function setSelected(value) {
        selectedPoints = clampSelected(value);
        render();
    }

    function fetchState() {
        if (!endpoint) {
            return;
        }

        var url = endpoint + (endpoint.indexOf('?') >= 0 ? '&' : '?') + '_=' + Date.now();

        window.fetch(url, {
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            })
            .then(function (payload) {
                if (!payload || !payload.success || !payload.data) {
                    throw new Error('Invalid loyalty response');
                }

                state = payload.data;

                if (!state.visible) {
                    roots.forEach(function (root) {
                        root.hidden = true;
                    });
                    return;
                }

                roots.forEach(function (root) {
                    root.hidden = false;
                });

                selectedPoints = clampSelected(selectedPoints);
                render();
            })
            .catch(function () {
                roots.forEach(function (root) {
                    root.classList.add('has-error');
                    var notice = root.querySelector('[data-loyalty-role="notice"]');
                    if (notice) {
                        notice.textContent = root.getAttribute('data-msg-error') || 'Не вдалося оновити бонуси.';
                    }
                });
            });
    }

    function scheduleRefresh() {
        window.clearTimeout(refreshTimer);
        refreshTimer = window.setTimeout(fetchState, 180);
    }

    function bindRoot(root) {
        var range = root.querySelector('[data-loyalty-input="range"]');
        var input = root.querySelector('[data-loyalty-input="number"]');
        var maxButton = root.querySelector('[data-loyalty-action="max"]');
        var clearButton = root.querySelector('[data-loyalty-action="clear"]');

        if (range) {
            range.addEventListener('input', function () {
                setSelected(range.value);
            });
        }

        if (input) {
            input.addEventListener('input', function () {
                setSelected(input.value);
            });
            input.addEventListener('blur', function () {
                render();
            });
        }

        if (maxButton) {
            maxButton.addEventListener('click', function (event) {
                event.preventDefault();
                setSelected(state ? state.max_points : 0);
            });
        }

        if (clearButton) {
            clearButton.addEventListener('click', function (event) {
                event.preventDefault();
                setSelected(0);
            });
        }
    }

    function init() {
        roots = Array.prototype.slice.call(document.querySelectorAll('[data-dneprit-loyalty-cart]'));
        if (!roots.length) {
            return;
        }

        endpoint = roots[0].getAttribute('data-endpoint') || '';
        userId = parseInt(roots[0].getAttribute('data-user-id'), 10) || 0;
        storageKey = 'dnepritloyalty_points_' + userId;
        selectedPoints = loadSelected();

        roots.forEach(bindRoot);

        document.addEventListener('submit', function () {
            syncOrderForms();
        }, true);

        if (window.jQuery) {
            window.jQuery(document).ajaxComplete(function (event, xhr, settings) {
                var url = settings && settings.url ? String(settings.url) : '';
                var data = settings && settings.data ? String(settings.data) : '';
                if (
                    url.indexOf('minishop2') !== -1 ||
                    data.indexOf('ms2_action') !== -1 ||
                    data.indexOf('cart/') !== -1
                ) {
                    scheduleRefresh();
                }
            });
        }

        ['miniShop2:cart:change', 'ms2:cart:change', 'ms2_cart_changed'].forEach(function (eventName) {
            document.addEventListener(eventName, scheduleRefresh);
        });

        var observer = new MutationObserver(function () {
            syncOrderForms();
        });
        observer.observe(document.documentElement, {
            childList: true,
            subtree: true
        });

        fetchState();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
