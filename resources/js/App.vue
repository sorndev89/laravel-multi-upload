<template lang="">
    <div>
        <h1>Home Page</h1>
        <hr>
        <MultiImageUploadVue />
    </div>
    <hr>

    <ImageListsVue />

</template>
<script>
import ImageListsVue from './components/ImageLists.vue'
import MultiImageUploadVue from './components/MultiImageUpload.vue'
export default {
    components:{
        MultiImageUploadVue,
        ImageListsVue
    }
}
</script>
<style lang="">
    
</style>