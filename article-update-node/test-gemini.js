// test-gemini.js - Test your Gemini API key and list available models

import axios from "axios";
import { GoogleGenerativeAI } from "@google/generative-ai";
import dotenv from "dotenv";

dotenv.config();

const API_KEY = process.env.GOOGLE_GEMINI_API_KEY;

async function testGeminiAPI() {
  console.log("🧪 Testing Gemini API Connection...\n");

  // Check if API key exists
  if (!API_KEY) {
    console.error("❌ GOOGLE_GEMINI_API_KEY not found in .env file");
    console.log("\n📝 Steps to fix:");
    console.log("1. Go to https://aistudio.google.com/app/apikey");
    console.log("2. Create or copy your API key");
    console.log("3. Add to .env file: GOOGLE_GEMINI_API_KEY=your_key_here");
    return;
  }

  console.log("✅ API Key found in .env");
  console.log(
    `📋 Key preview: ${API_KEY.substring(0, 10)}...${API_KEY.slice(-4)}\n`
  );

  // Test 1: List available models
  try {
    console.log("📋 Fetching available models...");
    const url = `https://generativelanguage.googleapis.com/v1beta/models?key=${API_KEY}`;
    const response = await axios.get(url);

    const allModels = response.data.models || [];
    const contentModels = allModels.filter((model) =>
      model.supportedGenerationMethods?.includes("generateContent")
    );

    console.log(
      `✅ Found ${contentModels.length} models that support generateContent:\n`
    );

    contentModels.forEach((model, i) => {
      const name = model.name.replace("models/", "");
      const description = model.description || "No description";
      console.log(`${i + 1}. ${name}`);
      console.log(`   Description: ${description.substring(0, 80)}...`);
      console.log(`   Input Token Limit: ${model.inputTokenLimit || "N/A"}`);
      console.log(
        `   Output Token Limit: ${model.outputTokenLimit || "N/A"}\n`
      );
    });

    // Test 2: Try generating content with the first available model
    if (contentModels.length > 0) {
      const testModel = contentModels[0].name.replace("models/", "");
      console.log(`\n🧪 Testing content generation with: ${testModel}...\n`);

      const genAI = new GoogleGenerativeAI(API_KEY);
      const model = genAI.getGenerativeModel({ model: testModel });

      const result = await model.generateContent({
        contents: [
          {
            role: "user",
            parts: [
              { text: "Write a one-sentence description of what AI is." },
            ],
          },
        ],
        generationConfig: {
          temperature: 0.7,
          maxOutputTokens: 100,
        },
      });

      const text = result.response.text();
      console.log("✅ Content generation successful!");
      console.log(`📝 Response: ${text}\n`);

      console.log("🎉 Your Gemini API is working perfectly!\n");
      console.log("✨ Recommended model for your app:", testModel);
    } else {
      console.log("⚠️  No models available for content generation");
    }
  } catch (error) {
    console.error("❌ API Test Failed:", error.message);

    if (error.response) {
      console.error("Status:", error.response.status);
      console.error("Error:", error.response.data);
    }

    console.log("\n💡 Troubleshooting:");
    console.log(
      "1. Verify your API key at: https://aistudio.google.com/app/apikey"
    );
    console.log("2. Make sure the Generative Language API is enabled");
    console.log(
      "3. Check if you have quota/billing issues (free tier should work)"
    );
    console.log("4. Try creating a new API key");
  }
}

// Run the test
testGeminiAPI();
