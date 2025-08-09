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

export async function refreshPayload(response) {
  await localforage.removeItem("racks");

  const allRacks ={};
  allRacks["materials"] = response.family;
  allRacks["rack"] = response.rack;
  // allRacks["details"] = response.details;
  await localforage.setItem("racks", allRacks);
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

export function blink(
  element,
  color1 = "#e74c3c",
  color2 = "#fefefe",
  speed = 800
) {
  element.animate({ stroke: color1 }, speed, mina.easein, () => {
    element.animate({ stroke: color2 }, speed, mina.easeout, () => {
      blink(element, color1, color2, speed); // recursive
    });
  });
}

export function floatUpDown(element, distance = 5, speed = 600) {
  element.animate(
    { transform: `t0,-${distance}` },
    speed,
    mina.easeinout,
    () => {
      element.animate(
        { transform: `t0,${distance}` },
        speed,
        mina.easeinout,
        () => {
          floatUpDown(element, distance, speed); // repeat
        }
      );
    }
  );
}

export function populateRecentlySearch({ rack, materials }) {
  console.log(materials);
  const recentlyViewedContainer = document.querySelector(".addRecentlyViewed");
  const spanViewed = document.querySelector(".recently-viewed");
  recentlyViewedContainer.innerHTML = ""; // Clear existing content
  if (!materials || materials.length === 0) {
    recentlyViewedContainer.innerHTML = "<p>No recently viewed materials.</p>";
    return;
  }
  spanViewed.innerHTML = `<h4 class="fw-semibold mb-3 text-primary">Recently Viewed: ${rack}</h4>`;

  // Create and append new elements for each material
  materials.forEach((material) => {
    const imageSrc = material.drawing
      ? material.drawing.startsWith("data:")
        ? material.drawing
        : `data:image/jpg;base64,${material.drawing}`
      : "assets/img/noImage.jpg"; // fallback

    const materialElement = document.createElement("div");
    materialElement.className = "col-12 col-md-3 col-lg-3 mb-3";
    materialElement.style.cursor = "pointer";
    materialElement.innerHTML = `
                 <div class="card shadow-sm h-100 text-start p-3">
                  <img src="${imageSrc}" class="card-img-top" style="height: 180px; object-fit: cover; border:1px solid #949191ff" alt="Material Image">
                  <div class="card-body p-2 small">
                    <h6 class="card-title text-primary mb-1">${
                      material.tag
                    }</h6>
                    <p class="mb-1 text-muted">Size: ${
                      material.size || "N/A"
                    }</p>
                    <p class="mb-0 text-muted">Supplier: ${
                      material.supplier || "N/A"
                    }</p>
                  </div>
                </div>
            `;

    materialElement.addEventListener("click", async () => {
      const tag = document.getElementById("tag");
      const form = document.getElementById("form");
      tag.value = material.tag; // Set the tag input value
      form.dispatchEvent(new Event("submit")); // Trigger the form submit event
    });
    recentlyViewedContainer.appendChild(materialElement);
  });
}

export function displayDetails(mat, rack) {
  // console.log(details)
  const image = new Image();
  image.src = mat.drawing
    ? `data:image/jpg;base64,${mat.drawing}`
    : "assets/img/noImage.jpg";
  image.alt = "Material Drawing";
  image.className = "material-img";

  // Wait for the image to load before showing the Swal
  image.onload = async () => {
    Swal.fire({
      title: `Material Details (${mat.id})`,
      html: `
      <div class="swal-body">
      <div class="row">
        <div class="d-flex justify-content-around align-content-around">
            <p><strong>Supplier:</strong> ${mat.customer}</p>
            <p><strong>Type:</strong> ${mat.materialType}</p>
            <p><strong>Size:</strong> ${mat.size}</p>
            <p><strong>Current Position:</strong> ${mat.position}</p>
        </div>
      </div>
        <div id="image-wrapper"></div>
      </div>
    `,
      customClass: {
        popup: "swal-wide",
      },
      showDenyButton: true,
      showCancelButton: true,
      confirmButtonText: "Material Found",
      denyButtonText: `Material Not Exist`,
      didOpen: () => {
        document.getElementById("image-wrapper").appendChild(image);
      },
    }).then(async (result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.isConfirmed) {
        try {
          const response = await post(`index.php?url=/withdraw`, {
            tag: mat.id,
            position: mat.position,
            rack: rack,
          });

          const swalViewData = {
            type: response.status ? "success" : "error",
            title: response.status ? "Successfuly updated" : "Error Occured",
            text: response.status
              ? "Please withdraw it on Lot Progress"
              : "Please Try again or Contact IT!",
          };

          const responsePayload = await post("index.php?url=/redraw", {
            rack: rack,
          });

          console.log(responsePayload, 'rack:'+rack);
          if (responsePayload) refresPayload(responsePayload);
h
          Swal.fire(swalViewData);
        } catch (error) {
          console.error("POST failed", error);
        }
      } else if (result.isDenied) {
        try {
          const response = await post(`index.php?url=/remove`, {
            tag: mat.id,
            position: mat.position,
            rack: rack,
          });

          const swalViewData = {
            type: response.status ? "success" : "error",
            title: response.status ? "Successfuly Removed!" : "Error Occured",
            text: response.status
              ? "Since it is not found desicion: remove"
              : "Please Try again or Contact IT!",
          };
          const responsePayload = await post("index.php?url=/redraw", {
            rack: rack,
          });
          if (responsePayload) refreshPayload(responsePayload);
          Swal.fire(swalViewData);
        } catch (error) {
          console.error("POST failed", error);
        }
      }

      setTimeout(() => {
        location.href = "index.php?url=/";
      }, 2000);
    });
  };
}
