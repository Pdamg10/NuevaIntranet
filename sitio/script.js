const Source_video = document.getElementById('video')
let canvas = document.getElementById('canvasOutput')

cv['onRuntimeInitialized'] = () => {

    let srcImage = new cv.Mat(Source_video.height, Source_video.width, cv.CV_8UC4);
    let dstImage = new cv.Mat(Source_video.height, Source_video.width, cv.CV_8UC1);
    let cap = new cv.VideoCapture(Source_video);

    const FPS = 30;
    function processSource_Video() {
        let begin_delay = Date.now();

        cap.read(srcImage);
        cv.cvtColor(srcImage, dstImage, cv.COLOR_RGBA2GRAY);
        cv.imshow(canvas, dstImage);

        let delay = 1000 / FPS - (Date.now() - begin_delay);
        setTimeout(processSource_Video, delay);
    };
    setTimeout(processSource_Video, 0);
};
