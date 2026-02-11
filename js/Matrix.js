// Get the canvas element and its 2D drawing context
const canvas = document.getElementById('Matrix');
const context = canvas.getContext('2d');

// Make the canvas fill the entire window
canvas.width = window.innerWidth;
canvas.height = window.innerHeight;

// Character sets to be used in the animation (Katakana, Latin letters, and numbers)
const katakana = 'アァカサタナハマヤャラワガザダバパイィキシチニヒミリヰギジヂビピウゥクスツヌフムユュルグズブヅプエェケセテネヘメレヱゲゼデベペオォコソトノホモヨョロヲゴゾドボポヴッン';
const latin = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
const nums = '0123456789';

// Merge all characters into a single string
const alphabet = katakana + latin + nums;

// Set font size and calculate the number of columns
const fontSize = 16;
const columns = canvas.width/fontSize;

// Initialize the rainDrops array with one drop per column
const rainDrops = [];
for( let x = 0; x < columns; x++ ) {
	rainDrops[x] = 1;
}
// Draw function: clears and redraws the canvas every frame
const draw = () => {
	// Create a semi-transparent black background for fading trail effect
	context.fillStyle = 'rgba(0, 0, 0, 0.05)';
	context.fillRect(0, 0, canvas.width, canvas.height);
	// Set text style Green letters
	context.fillStyle = '#0F0';
	context.font = fontSize + 'px monospace';
	// Loop through drops and draw each one
	for(let i = 0; i < rainDrops.length; i++) {
		const text = alphabet.charAt(Math.floor(Math.random() * alphabet.length));
		context.fillText(text, i*fontSize, rainDrops[i]*fontSize);
		
		// Reset drop to the top randomly after it leaves the screen
		if(rainDrops[i]*fontSize > canvas.height && Math.random() > 0.975){
			rainDrops[i] = 0;
		}
		// Move drop down by one row
		rainDrops[i]++;
	}
};
// Run draw function every 30 milliseconds to create animation
setInterval(draw, 30);