// ✅ Correct import
import { post, get, showLoader, closeLoader} from "./module.js";
showLoader("Loading view...");
document.addEventListener("DOMContentLoaded",async () => {
  closeLoader(); // ✅ Close loader after DOM is ready

});
