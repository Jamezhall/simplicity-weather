document.addEventListener('DOMContentLoaded', function () {
  if (typeof SimplicityWeatherBadge === 'undefined') {
    return;
  }

  var badges = document.querySelectorAll('.simplicity-weather-badge.is-loading');

  badges.forEach(function (badge) {
    var formData = new window.FormData();
    formData.append('action', 'simplicity_weather_badge');
    formData.append('location', badge.getAttribute('data-location') || '');
    formData.append('fields', badge.getAttribute('data-fields') || '');
    formData.append('separator', badge.getAttribute('data-separator') || ', ');

    window.fetch(SimplicityWeatherBadge.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (payload) {
        badge.classList.remove('is-loading');
        badge.removeAttribute('aria-busy');

        if (!payload || !payload.success || !payload.data || !payload.data.text) {
          badge.textContent = SimplicityWeatherBadge.errorText;
          return;
        }

        badge.textContent = payload.data.text;
      })
      .catch(function () {
        badge.classList.remove('is-loading');
        badge.removeAttribute('aria-busy');
        badge.textContent = SimplicityWeatherBadge.errorText;
      });
  });
});
