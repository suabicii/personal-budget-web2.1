let difference = document.getElementById("difference");
let positiveFeedback = document.querySelector(".onPlus");
let negativeFeedback = document.querySelector(".onMinus");
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
      sum += parseFloat(income.textContent); // zamiana string na liczbę (float)
    });
  }
  return sum;
};

const sumExpenses = (expenses) => {
  let sum = 0;
  if (expenses != null) {
    expenses.forEach((expense) => {
      sum += parseFloat(expense.textContent);
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
    ? parseFloat(document.querySelector("#Salary").textContent)
    : 0;
  interest = document.querySelector("#Interest")
    ? parseFloat(document.querySelector("#Interest").textContent)
    : 0;
  allegro = document.querySelector("#Allegro")
    ? parseFloat(document.querySelector("#Allegro").textContent)
    : 0;
  anotherIncomes = document.querySelector("#Another-Incomes")
    ? parseFloat(document.querySelector("#Another-Incomes").textContent)
    : 0;
} else {
  const salaries = document.querySelectorAll(".Salary");
  salary = sumIncomes(salaries);

  const interests = document.querySelectorAll(".Interest");
  interest = sumIncomes(interests);

  const allegroIncomes = document.querySelectorAll(".Allegro");
  allegro = sumIncomes(allegroIncomes);

  const anotherIncomesAll = document.querySelectorAll(".Another-Incomes");
  anotherIncomes = sumIncomes(anotherIncomesAll);
}

const drawChartOfIncomes = () => {
  let data = google.visualization.arrayToDataTable([
    ["Kategoria", "Kwota"],
    ["Wynagrodzenie", salary != null ? salary : 0],
    ["Odsetki bankowe", interest != null ? interest : 0],
    ["Sprzedaż na allegro", allegro != null ? allegro : 0],
    ["Inne", anotherIncomes != null ? anotherIncomes : 0],
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
    ? parseFloat(document.querySelector("#Food").textContent)
    : 0;
  apartments = document.querySelector("#Apartments")
    ? parseFloat(document.querySelector("#Apartments").textContent)
    : 0;
  transport = document.querySelector("#Transport")
    ? parseFloat(document.querySelector("#Transport").textContent)
    : 0;
  telecommunication = document.querySelector("#Telecommunication")
    ? parseFloat(document.querySelector("#Telecommunication").textContent)
    : 0;
  health = document.querySelector("#Health")
    ? parseFloat(document.querySelector("#Health").textContent)
    : 0;
  clothes = document.querySelector("#Clothes")
    ? parseFloat(document.querySelector("#Clothes").textContent)
    : 0;
  hygiene = document.querySelector("#Hygiene")
    ? parseFloat(document.querySelector("#Hygiene").textContent)
    : 0;
  kids = document.querySelector("#Kids")
    ? parseFloat(document.querySelector("#Kids").textContent)
    : 0;
  recreation = document.querySelector("#Recreation")
    ? parseFloat(document.querySelector("#Recreation").textContent)
    : 0;
  trip = document.querySelector("#Trip")
    ? parseFloat(document.querySelector("#Trip").textContent)
    : 0;
  courses = document.querySelector("#Courses")
    ? parseFloat(document.querySelector("#Courses").textContent)
    : 0;
  books = document.querySelector("#Books")
    ? parseFloat(document.querySelector("#Books").textContent)
    : 0;
  savings = document.querySelector("#Savings")
    ? parseFloat(document.querySelector("#Savings").textContent)
    : 0;
  retirement = document.querySelector("#For-Retirement")
    ? parseFloat(document.querySelector("#For-Retirement").textContent)
    : 0;
  debts = document.querySelector("#Debt-Repayment")
    ? parseFloat(document.querySelector("#Debt-Repayment").textContent)
    : 0;
  gift = document.querySelector("#Gift")
    ? parseFloat(document.querySelector("#Gift").textContent)
    : 0;
  anotherExpenses = document.querySelector("#Another-Expenses")
    ? parseFloat(document.querySelector("#Another-Expenses").textContent)
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

  const retirementAll = document.querySelectorAll(".For-Retirement");
  retirement = sumExpenses(retirementAll);

  const debtsAll = document.querySelectorAll(".Debt-Repayment");
  debts = sumExpenses(debtsAll);

  const giftAll = document.querySelectorAll(".Gift");
  gift = sumExpenses(giftAll);

  const anotherExpensesAll = document.querySelectorAll(".Another-Expenses");
  anotherExpenses = sumExpenses(anotherExpensesAll);
}

const drawChartOfExpenses = () => {
  let data = google.visualization.arrayToDataTable([
    ["Kategoria", "Kwota"],
    ["Jedzenie", food != null ? food : 0],
    ["Mieszkanie", apartments != null ? apartments : 0],
    ["Transport", transport != null ? transport : 0],
    ["Telekomunikacja", telecommunication != null ? telecommunication : 0],
    ["Opieka zdrowotna", health != null ? health : 0],
    ["Ubranie", clothes != null ? clothes : 0],
    ["Higiena", hygiene != null ? hygiene : 0],
    ["Dzieci", kids != null ? kids : 0],
    ["Rozrywka", recreation != null ? recreation : 0],
    ["Wycieczka", trip != null ? trip : 0],
    ["Szkolenia", courses != null ? courses : 0],
    ["Książki", books != null ? books : 0],
    ["Oszczędności", savings != null ? savings : 0],
    ["Na złotą jesień, czyli emeryturę", retirement != null ? retirement : 0],
    ["Spłata długów", debts != null ? debts : 0],
    ["Darowizna", gift != null ? gift : 0],
    ["Inne wydatki", anotherExpenses != null ? anotherExpenses : 0],
  ]);

  let options = { title: "Wydatki", width: chartWidth, height: 400 };

  let chart = new google.visualization.PieChart(
    document.getElementById("piechart-expenses")
  );
  chart.draw(data, options);
};
google.charts.setOnLoadCallback(drawChartOfIncomes);
google.charts.setOnLoadCallback(drawChartOfExpenses);
