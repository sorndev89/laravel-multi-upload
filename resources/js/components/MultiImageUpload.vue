<template>
  <div>
    <h3>Upload Multiple Images</h3>
    <input type="file" multiple @change="handleFileChange" />
    <button :disabled="uploading" @click="uploadImages">Upload</button>

    <div v-if="images.length" style="margin-top: 20px;">
      <h4>Preview and Status</h4>
      <div v-for="(img, index) in images" :key="index" style="margin-bottom: 15px;">
        <img :src="previewImages[index]" alt="Image preview" style="width: 100px; height: auto; margin-right: 10px;" />
        <span>Status: <strong>{{ uploadStatuses[index].status }}</strong></span>
        <span v-if="uploadStatuses[index].status === 'Uploading'">
          ({{ uploadStatuses[index].progress }}%)
        </span>
        <span v-if="uploadStatuses[index].status === 'Error'">
          (Attempts: {{ uploadStatuses[index].attempts }})
        </span>
        <button
          v-if="uploadStatuses[index].status === 'Error'"
          @click="retryUpload(index)"
          style="margin-left: 10px;"
        >
          Retry
        </button>
      </div>
    </div>

    <p v-if="message" :class="{ success: success, error: !success }">{{ message }}</p>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      images: [], // Array of selected files
      previewImages: [], // Array of preview image URLs
      uploadStatuses: [], // Upload statuses with progress, attempts, and state
      message: "", // Global status message
      success: false, // Indicates if the global message is a success or error
      uploading: false, // Controls the upload button state
      maxRetries: 5, // Maximum retry attempts
    };
  },
  methods: {
    handleFileChange(event) {
      this.images = Array.from(event.target.files); // Store selected files
      this.previewImages = this.images.map((file) => URL.createObjectURL(file)); // Generate preview URLs
      this.uploadStatuses = this.images.map(() => ({
        status: "Pending", // Initial status for each file
        attempts: 0, // Track upload attempts
        progress: 0, // Track upload progress (percentage)
      }));
    },
    async uploadImages() {
      if (!this.images.length) {
        this.message = "Please select images to upload.";
        this.success = false;
        return;
      }

      this.uploading = true; // Disable the upload button
      this.message = "";
      this.success = true;

      for (let i = 0; i < this.images.length; i++) {
        if (this.uploadStatuses[i].status === "Success") continue; // Skip already uploaded files
        await this.uploadWithProgress(i);
      }

      this.uploading = false;

      // Clear successful uploads
      this.clearSuccessfulUploads();
    },
    async uploadWithProgress(index) {
      while (this.uploadStatuses[index].attempts < this.maxRetries) {
        this.uploadStatuses[index].attempts++;
        this.uploadStatuses[index].status = "Uploading";

        const formData = new FormData();
        formData.append("images[]", this.images[index]);

        try {
          const response = await axios.post("/api/upload-images", formData, {
            headers: { "Content-Type": "multipart/form-data" },
            onUploadProgress: (progressEvent) => {
              const percentage = Math.round((progressEvent.loaded * 100) / progressEvent.total);
              this.uploadStatuses[index].progress = percentage; // Update progress
            },
          });

          if (response.data.success) {
            this.uploadStatuses[index].status = "Success";
            return;
          } else {
            throw new Error("Upload failed on server.");
          }
        } catch (error) {
          console.error(`Upload attempt ${this.uploadStatuses[index].attempts} failed:`, error);

          if (this.uploadStatuses[index].attempts >= this.maxRetries) {
            this.uploadStatuses[index].status = "Error"; // Mark as failed after max retries
          }
        }
      }
    },
    retryUpload(index) {
      this.uploadStatuses[index].status = "Pending";
      this.uploadStatuses[index].attempts = 0;
      this.uploadStatuses[index].progress = 0;
      this.uploadWithProgress(index);
    },
    clearSuccessfulUploads() {
      const remainingImages = [];
      const remainingPreviews = [];
      const remainingStatuses = [];

      this.images.forEach((_, index) => {
        if (this.uploadStatuses[index].status !== "Success") {
          remainingImages.push(this.images[index]);
          remainingPreviews.push(this.previewImages[index]);
          remainingStatuses.push(this.uploadStatuses[index]);
        }
      });

      this.images = remainingImages;
      this.previewImages = remainingPreviews;
      this.uploadStatuses = remainingStatuses;

      this.message = "Successful uploads cleared from the list.";
      this.success = true;
    },
  },
};
</script>

<style>
.success {
  color: green;
}
.error {
  color: red;
}
</style>
