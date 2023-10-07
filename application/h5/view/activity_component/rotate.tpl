<style>
    /*转盘css*/
    .luck_rotate{
        position: relative;
        width: 100%;
    }
    .luck_rotate>div{
        margin: 0 auto;
        text-align: center;
        width: 80%;
    }

    .luck_rotate .luck_rotate_btn{
        width: 2rem;
        margin: 0 auto;
        display: block;
        position: absolute;
        top: 1.55rem;
        left: 2.21rem;
    }
</style>

<div class="luck_rotate">
    <div>
        <img id="rotate" class="luck_rotate_content" src="__IMAGES__/live_act/gratitude/zhuanpan_033.png" alt="">
        <img class="luck_rotate_btn {$is_start}" src="__IMAGES__/live_act/gratitude/zhizheng_033.png" alt="">
    </div>
</div>

<script src="__H5__/js/activity/Turntable.js"></script>