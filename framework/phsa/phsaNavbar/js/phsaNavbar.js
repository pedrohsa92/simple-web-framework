





document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".phsaScrollNav").forEach((scrollNav) => {
    let isDragging = false;
    let startX;
    let scrollLeft;

    scrollNav.addEventListener("mousedown", (e) => {
      isDragging = true;
      scrollNav.classList.add("dragging");
      startX = e.pageX - scrollNav.offsetLeft;
      scrollLeft = scrollNav.scrollLeft;
    });

    scrollNav.addEventListener("mouseleave", () => {
      isDragging = false;
      scrollNav.classList.remove("dragging");
    });

    scrollNav.addEventListener("mouseup", () => {
      isDragging = false;
      scrollNav.classList.remove("dragging");
    });

    scrollNav.addEventListener("mousemove", (e) => {
      if (!isDragging) return;
      e.preventDefault();
      const x = e.pageX - scrollNav.offsetLeft;
      const walk = (x - startX) * 2;
      scrollNav.scrollLeft = scrollLeft - walk;
    });

    scrollNav.addEventListener("click", (e) => {
      const item = e.target.closest(".phsaScrollItem");
      if (!item) return;

      const menuName = scrollNav.getAttribute("data-menu");

      document.querySelectorAll(`[data-menu="${menuName}"] .phsaScrollItem`).forEach((el) => el.classList.remove("active"));
      item.classList.add("active");

      const parentTabContent = scrollNav.closest(".phsaTabItem.active") || document.querySelector(".phsaTabContent");
      if (parentTabContent) {
        const tabs = parentTabContent.querySelectorAll(".phsaTabItem");
        tabs.forEach((tab) => tab.classList.remove("active"));
      }

      const targetId = item.getAttribute("data-target");
      const targetTab = document.getElementById(targetId);
      if (targetTab) targetTab.classList.add("active");

      item.scrollIntoView({
        behavior: "smooth",
        block: "nearest",
        inline: "center",
      });

      activateSubmenu(targetTab);
    });
  });

  function activateSubmenu(targetTab) {
    if (!targetTab) return;

    const activeSubmenu = targetTab.querySelector(".phsaScrollNav .phsaScrollItem.active");
    if (activeSubmenu) {
      const submenuTargetId = activeSubmenu.getAttribute("data-target");
      const submenuContent = targetTab.querySelector(`#${submenuTargetId}`);
      if (submenuContent) {
        targetTab.querySelectorAll(".phsaTabItem").forEach((tab) => tab.classList.remove("active"));
        submenuContent.classList.add("active");

        activateSubmenu(submenuContent);
      }
    }
  }
});