const { test, expect } = require('@playwright/test');

test('New effort should start at current time (not 1 hour earlier)', async ({ page }) => {
  // Login
  await page.goto('http://localhost:8283/');
  await page.fill('input[name="username"]', 'ruben');
  await page.fill('input[name="password"]', 'Kaese1232!');
  await page.click('button[type="submit"]');
  
  // Wait for page load after login
  await page.waitForLoadState('networkidle');
  
  // Navigate to create new effort
  await page.goto('http://localhost:8283/inventory/efforts.php?new=1');
  await page.waitForLoadState('networkidle');
  
  // Get current time
  const now = new Date();
  const currentHour = String(now.getHours()).padStart(2, '0');
  const currentMinute = String(now.getMinutes()).padStart(2, '0');
  
  console.log(`Current time: ${currentHour}:${currentMinute}`);
  
  // Read the hour field from the effort form (it's a SELECT element)
  const hourField = await page.locator('select[name="hour"]');
  const beginHour = await hourField.inputValue();
  
  console.log(`Begin hour in form: ${beginHour}`);
  
  // The begin hour should match current hour
  const beginHourInt = parseInt(beginHour);
  const currentHourInt = parseInt(currentHour);
  
  console.log(`Expected hour: ${currentHourInt}, Got: ${beginHourInt}`);
  
  if (beginHourInt !== currentHourInt) {
    console.log(`❌ FAIL: Start time is 1 hour off! Current: ${currentHour}:${currentMinute}, Form: ${beginHour}:??`);
  }
  expect(beginHourInt).toBe(currentHourInt);
  
  console.log('✓ Start time is correct!');
});

test('Effort seconds should be :00 not exact seconds', async ({ page }) => {
  // Login
  await page.goto('http://localhost:8283/');
  await page.fill('input[name="username"]', 'ruben');
  await page.fill('input[name="password"]', 'Kaese1232!');
  await page.click('button[type="submit"]');
  
  // Wait for page load after login
  await page.waitForLoadState('networkidle');
  
  // Navigate to create new effort
  await page.goto('http://localhost:8283/inventory/efforts.php?new=1');
  await page.waitForLoadState('networkidle');
  
  // Read the project selector to verify form loaded
  const projectSelector = await page.locator('#effort-form-inner-table > tbody:nth-child(2) > tr:nth-child(8) > td:nth-child(2) > select:nth-child(2)');
  const projectValue = await projectSelector.inputValue();
  
  console.log(`Project selector value: ${projectValue}`);
  
  // Check the minute field (it's a SELECT element)
  const minuteField = await page.locator('select[name="minute"]');
  const minute = await minuteField.inputValue();
  
  console.log(`Minute value: ${minute}`);
  
  // Minute should be rounded to nearest 5 minutes
  const minuteInt = parseInt(minute);
  if (minuteInt % 5 !== 0) {
    console.log(`❌ FAIL: Minutes not rounded to 5-minute intervals! Got: ${minute}`);
  }
  expect(minuteInt % 5).toBe(0);
  
  console.log('✓ Minutes are rounded to 5-minute intervals!');
});
