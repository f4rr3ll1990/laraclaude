import { writeFile } from 'fs/promises';
import { init, getAuthToken } from "@heyputer/puter.js/src/init.cjs";

const authToken = await getAuthToken(); // performs browser based auth and retrieves token (requires browser)

console.log(authToken);

const puter = init(authToken); // uses your auth token

puter.ai.txt2img("A peaceful mountain landscape at sunset", { model: "gpt-image-2", quality: "low" })
// Добавлено ключевое слово async перед параметром функции
.then(async (imageElement) => {
    try {
        const base64Data = imageElement.src.replace(/^data:image\/\w+;base64,/, "");
        const buffer = Buffer.from(base64Data, 'base64');

        // Теперь await здесь сработает корректно
        await writeFile('image.png', buffer);
        console.log('Картинка успешно сохранена!');
    } catch (error) {
        console.error('Ошибка сохранения файла:', error);
    }
})
.catch(err => {
    console.error("Ошибка при генерации изображения!");
    console.error(err);
});
