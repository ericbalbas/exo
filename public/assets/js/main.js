// ✅ Correct import
import { post, get, showLoader, closeLoader, populateRecentlySearch} from "./module.js";
showLoader("Loading view...");
document.addEventListener("DOMContentLoaded",async () => {
  const allRacks = (await localforage.getItem("racks")) || {};

  closeLoader(); // ✅ Close loader after DOM is ready
  const form = document.getElementById("form");
  const tagInput = document.getElementById("tag");

  tagInput.focus();
  tagInput.value = ""; 

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    const tag = tagInput.value.trim();
    if (!tag) {
        Swal.fire({
          icon: "warning",
          title: "Oops...",
          text: "Tag is required!",
        });
        return;
    }
    
    try {
      const response = await post("index.php?url=/show", { tag:tag });
      if(!response.status) throw new Error("INVALID TAG!!!");
      allRacks['materials'] = response.family;
      allRacks['rack'] = response.rack;
      allRacks['details'] = response.details;
      await localforage.setItem("racks", allRacks);
      window.location.href = 'index.php?url=/check';

    } catch (err) {
      console.error("POST failed", err);
    }


    
  });

  if (allRacks) populateRecentlySearch(allRacks);

});
