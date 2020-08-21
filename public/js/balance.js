let dropdownHeadingBtn = document.querySelector(".dropdown-heading");
let dropdownOptions = document.querySelectorAll(".dropdown-item");
let difference = document.getElementById("difference");
let positiveFeedback = document.querySelector(".onPlus");
let negativeFeedback = document.querySelector(".onMinus");
let period = document.querySelector(".period");
let sumOfIncomes = document.getElementById("incomes");
let sumOfExpenses = document.getElementById("expenses");

difference.textContent = (
  sumOfIncomes.textContent - sumOfExpenses.textContent
).toFixed(2);

const getFeedback = () => {
  if (difference.textContent > 0) {
    positiveFeedback.style.display = "inline";
    negativeFeedback.style.display = "none";
    difference.classList.add("bg-success");
    difference.classList.remove("bg-danger");
  } else {
    positiveFeedback.style.display = "none";
    negativeFeedback.style.display = "inline";
    difference.classList.add("bg-danger");
    difference.classList.remove("bg-success");
  }
};

getFeedback();

// Wykresy kołowe

// Load google charts
google.charts.load("current", { packages: ["corechart"] });

let chartWidth;

if (window.innerWidth > 1200) {
  chartWidth = 550;
} else if (window.innerWidth >= 768 && window.innerWidth <= 1200) {
  chartWidth = 400;
} else if (window.innerWidth < 768) {
  chartWidth = 350;
}

// Sumowanie przychodów/wydatków przy widoku szczegółowym
const sumIncomes = (incomes) => {
  let sum = 0;
  if (incomes != null) {
    incomes.forEach((income) => {
      sum += income.textContent;
    });
  }
  return sum;
};

const sumExpenses = (expenses) => {
  let sum = 0;
  if (expenses != null) {
    expenses.forEach((expense) => {
      sum += expense.textContent;
    });
  }
  return sum;
};

// Zmienne przypisane do poszczególnych kategorii
let salary;
let interest;
let allegro;
let anotherIncomes;
if (!particularView) {
  salary = document.querySelector("#Salary")
    ? document.querySelector("#Salary").textContent
    : 0;
  interest = document.querySelector("#Interest")
    ? document.querySelector("#Interest").textContent
    : 0;
  allegro = document.querySelector("#Allegro")
    ? document.querySelector("#Allegro").textContent
    : 0;
  anotherIncomes = document.querySelector("#another-incomes")
    ? document.querySelector("#another-incomes").textContent
    : 0;
} else {
  const salaries = document.querySelectorAll(".Salary");
  salary = sumIncomes(salaries);
  const interests = document.querySelectorAll(".Interest");
  interest = sumIncomes(interests);
  const allegroIncomes = document.querySelectorAll(".Allegro");
  allegro = sumIncomes(allegroIncomes);
  const anotherIncomesAll = document.querySelectorAll(".another-incomes");
  anotherIncomes = sumIncomes(anotherIncomesAll);
}

const drawChartOfIncomes = () => {
  let data = google.visualization.arrayToDataTable([
    ["Kategoria", "Kwota"],
    ["Wynagrodzenie", salary != null ? parseFloat(salary) : 0], // Zamiana string na liczbę
    ["Odsetki bankowe", interest != null ? parseFloat(interest) : 0],
    ["Sprzedaż na allegro", allegro != null ? parseFloat(allegro) : 0],
    ["Inne", anotherIncomes != null ? parseFloat(anotherIncomes) : 0],
  ]);

  let options = { title: "Przychody", width: chartWidth, height: 400 };

  let chart = new google.visualization.PieChart(
    document.getElementById("piechart-incomes")
  );
  chart.draw(data, options);
};

// Zmienne przypisane do poszczególnych kategorii
let food;
let apartments;
let transport;
let telecommunication;
let health;
let clothes;
let hygiene;
let kids;
let recreation;
let trip;
let courses;
let books;
let savings;
let retirement;
let debts;
let gift;
let anotherExpenses;

if (!particularView) {
  food = document.querySelector("#Food")
    ? document.querySelector("#Food").textContent
    : 0;
  apartments = document.querySelector("#Apartments")
    ? document.querySelector("#Apartments").textContent
    : 0;
  transport = document.querySelector("#Transport")
    ? document.querySelector("#Transport").textContent
    : 0;
  telecommunication = document.querySelector("#Telecommunication")
    ? document.querySelector("#Telecommunication").textContent
    : 0;
  health = document.querySelector("#Health")
    ? document.querySelector("#Health").textContent
    : 0;
  clothes = document.querySelector("#Clothes")
    ? document.querySelector("#Clothes").textContent
    : 0;
  hygiene = document.querySelector("#Hygiene")
    ? document.querySelector("#Hygiene").textContent
    : 0;
  kids = document.querySelector("#Kids")
    ? document.querySelector("#Kids").textContent
    : 0;
  recreation = document.querySelector("#Recreation")
    ? document.querySelector("#Recreation").textContent
    : 0;
  trip = document.querySelector("#Trip")
    ? document.querySelector("#Trip").textContent
    : 0;
  courses = document.querySelector("#Courses")
    ? document.querySelector("#Courses").textContent
    : 0;
  books = document.querySelector("#Books")
    ? document.querySelector("#Books").textContent
    : 0;
  savings = document.querySelector("#Savings")
    ? document.querySelector("#Savings").textContent
    : 0;
  retirement = document.querySelector("#retirement")
    ? document.querySelector("#retirement").textContent
    : 0;
  debts = document.querySelector("#debt-repayment")
    ? document.querySelector("#debt-repayment").textContent
    : 0;
  gift = document.querySelector("#Gift")
    ? document.querySelector("#Gift").textContent
    : 0;
  anotherExpenses = document.querySelector("#another-expenses")
    ? document.querySelector("#another-expenses").textContent
    : 0;
} else {
  const foodAll = document.querySelectorAll(".Food");
  food = sumExpenses(foodAll);
  const apartmentsAll = document.querySelectorAll(".Apartments");
  apartments = sumExpenses(apartmentsAll);
  const transportAll = document.querySelectorAll(".Transport");
  transport = sumExpenses(transportAll);
  const telecommunicationAll = document.querySelectorAll(".Telecommunication");
  telecommunication = sumExpenses(telecommunicationAll);
  const healthAll = document.querySelectorAll(".Health");
  health = sumExpenses(healthAll);
  const clothesAll = document.querySelectorAll(".Clothes");
  clothes = sumExpenses(clothesAll);
  const hygieneAll = document.querySelectorAll(".Hygiene");
  hygiene = sumExpenses(hygieneAll);
  const kidsAll = document.querySelectorAll(".Kids");
  kids = sumExpenses(kidsAll);
  const recreationAll = document.querySelectorAll(".Recreation");
  recreation = sumExpenses(recreationAll);
  const tripAll = document.querySelectorAll(".Trip");
  trip = sumExpenses(tripAll);
  const coursesAll = document.querySelectorAll(".Courses");
  courses = sumExpenses(coursesAll);
  const booksAll = document.querySelectorAll(".Books");
  books = sumExpenses(booksAll);
  const savingsAll = document.querySelectorAll(".Savings");
  savings = sumExpenses(savingsAll);
  const retirementAll = document.querySelectorAll(".retirement");
  retirement = sumExpenses(retirementAll);
  const debtsAll = document.querySelectorAll(".debt-repayment");
  debts = sumExpenses(debtsAll);
  const giftAll = document.querySelectorAll(".Gift");
  gift = sumExpenses(giftAll);
  const anotherExpensesAll = document.querySelectorAll(".another-expenses");
  anotherExpenses = sumExpenses(anotherExpensesAll);
}

const drawChartOfExpenses = () => {
  let data = google.visualization.arrayToDataTable([
    ["Kategoria", "Kwota"],
    ["Jedzenie", food != null ? parseFloat(food) : 0],
    ["Mieszkanie", apartments != null ? parseFloat(apartments) : 0],
    ["Transport", transport != null ? parseFloat(transport) : 0],
    [
      "Telekomunikacja",
      telecommunication != null ? parseFloat(telecommunication) : 0,
    ],
    ["Opieka zdrowotna", health != null ? parseFloat(health) : 0],
    ["Ubranie", clothes != null ? parseFloat(clothes) : 0],
    ["Higiena", hygiene != null ? parseFloat(hygiene) : 0],
    ["Dzieci", kids != null ? parseFloat(kids) : 0],
    ["Rozrywka", recreation != null ? parseFloat(recreation) : 0],
    ["Wycieczka", trip != null ? parseFloat(trip) : 0],
    ["Szkolenia", courses != null ? parseFloat(courses) : 0],
    ["Książki", books != null ? parseFloat(books) : 0],
    ["Oszczędności", savings != null ? parseFloat(savings) : 0],
    [
      "Na złotą jesień, czyli emeryturę",
      retirement != null ? parseFloat(retirement) : 0,
    ],
    ["Spłata długów", debts != null ? parseFloat(debts) : 0],
    ["Darowizna", gift != null ? parseFloat(gift) : 0],
    ["Inne wydatki", anotherExpenses != null ? parseFloat(anotherExpenses) : 0],
  ]);

  let options = { title: "Wydatki", width: chartWidth, height: 400 };

  let chart = new google.visualization.PieChart(
    document.getElementById("piechart-expenses")
  );
  chart.draw(data, options);
};
google.charts.setOnLoadCallback(drawChartOfIncomes);
google.charts.setOnLoadCallback(drawChartOfExpenses);
