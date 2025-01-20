<template>
  <div>
    <h3>Uploaded Images</h3>
    <div v-if="loading">Loading...</div>
    <div v-else-if="images.length === 0">No images uploaded yet.</div>
    <div v-else>
      <div v-for="(image, index) in images" :key="index" style="margin-bottom: 15px;">
        <img :src="image.url" :alt="image.name" style="width: 100px; height: auto; margin-right: 10px;" />
        <span>{{ image.name }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      images: [], // List of uploaded images
      loading: true, // Show loading state while fetching data
    };
  },
  async mounted() {
    try {
      const response = await axios.get("/api/images");
      this.images = response.data;
    } catch (error) {
      console.error("Failed to fetch images:", error);
    } finally {
      this.loading = false;
    }
  },
};
</script>

<style>
img {
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 5px;
}
</style>
