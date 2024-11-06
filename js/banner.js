document.addEventListener("DOMContentLoaded", function () {
  const wpHeaderEnd = document.querySelector(".wp-header-end");
  if (!wpHeaderEnd) {
    return;
  }

  const bannerRoot = document.createElement("div");
  bannerRoot.id = "tsdk_banner";
  bannerRoot.classList.add("feedzy-banner-dashboard");
  wpHeaderEnd.insertAdjacentElement("afterend", bannerRoot);

  document.dispatchEvent(new Event("themeisle:banner:init"));
});
