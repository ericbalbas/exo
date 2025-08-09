let counter = 0;
let interval;

export function showLoader(title = "Loading...") {
  Swal.fire({
    title: title,
    html: "<b>0</b> seconds passed.",
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
      const counterEl = Swal.getHtmlContainer().querySelector("b");
      interval = setInterval(() => {
        counter++;
        counterEl.textContent = counter;
      }, 1000);
    },
    willClose: () => {
      clearInterval(interval);
      counter = 0;
    },
  });
}

export function closeLoader() {
  Swal.close();
  clearInterval(interval);
  counter = 0;
}


export async function post(url = "", data = {}) {
  try {
    showLoader("Processing request...");
    const response = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
    });

    if (!response.ok)
      throw new Error(
        `Error @ module.js HTTP error! status:${response.status}`
      );

    await closeLoader(); // ✅ WAIT until Swal is fully closed
    return await response.json();
  } catch (error) {
    console.error("POST failed", error);
    await closeLoader(); // ✅ still close in error
    return null;
  }
}

export async function get(url = "") {
  try {
    showLoader("Fetching data...");
    const response = await fetch(url);

    if (!response.ok)
      throw new Error(
        `Error @ module.js HTTP error! status:${response.status}`
      );

    return await response.json();
  } catch (error) {
    console.error("GET failed", error);
    return null;
  } finally {
    await closeLoader(); // ✅ make sure to await here too
  }
}
