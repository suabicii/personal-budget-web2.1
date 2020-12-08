// Ograniczenie zakresu zmiennych/funkcji w celu uniknięcia redeklaracji
{
  let difference = document.getElementById("difference");
  let positiveFeedback = document.querySelector(".onPlus");
  let negativeFeedback = document.querySelector(".onMinus");
  let sumOfIncomes = document.getElementById("incomes");
  let sumOfExpenses = document.getElementById("expenses");

  // Domyślna data końca okresu w formularzu wyboru niestandardowych dat
  let date = new Date();
  let month =
    date.getMonth() < 10 ? "0" + date.getMonth() : date.getMonth() + 1;
  let day = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();

  document
    .getElementById("end-date")
    .setAttribute("value", date.getFullYear() + "-" + month + "-" + day);

  /** Zapisywanie do tablic przychodów i wydatków */

  let rowsWithIncomes = [...document.querySelectorAll(".income")];
  let incomes = [];
  let rowsWithExpenses = [...document.querySelectorAll(".expense")];
  let expenses = [];

  // Sumowanie przychodów/wydatków przy widoku szczegółowym
  const sumIncomes = (incomes) => {
    let sum = 0;
    if (incomes != null) {
      incomes.forEach((income) => {
        sum += parseFloat(income.textContent);
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

  if (!particularView) {
    for (let i = 0; i < rowsWithIncomes.length; i++) {
      incomes.push({
        name: rowsWithIncomes[i].children[1].children[0].textContent,
        amount: parseFloat(rowsWithIncomes[i].children[2].textContent), // zamiana string
        // na liczbę (float)
      });
    }

    for (let i = 0; i < rowsWithExpenses.length; i++) {
      expenses.push({
        name: rowsWithExpenses[i].children[1].children[0].textContent,
        amount: parseFloat(rowsWithExpenses[i].children[2].textContent),
      });
    }
  } else {
    // Sortowanie wierszy z tabeli z przychodami wg kategorii
    rowsWithIncomes.sort((a, b) => {
      let nameA = a.children[1].textContent.toUpperCase();
      let nameB = b.children[1].textContent.toUpperCase();

      if (nameA < nameB) {
        return -1;
      }
      if (nameA > nameB) {
        return 1;
      }

      return 0;
    });

    incomes.push({
      name: rowsWithIncomes[0].children[1].children[0].textContent,
      amount: sumIncomes(
        document.querySelectorAll(
          "." + rowsWithIncomes[0].children[3].className
        )
      ),
    });

    for (let i = 1; i <= rowsWithIncomes.length - 1; i++) {
      if (
        rowsWithIncomes[i].children[1].textContent !=
        rowsWithIncomes[i - 1].children[1].textContent
      ) {
        incomes.push({
          name: rowsWithIncomes[i].children[1].children[0].textContent,
          amount: sumIncomes(
            document.querySelectorAll(
              "." + rowsWithIncomes[i].children[3].className
            )
          ),
        });
      }
    }

    // Sortowanie wierszy z tabeli z wydatkami wg kategorii
    rowsWithExpenses.sort((a, b) => {
      let nameA = a.children[1].textContent.toUpperCase();
      let nameB = b.children[1].textContent.toUpperCase();

      if (nameA < nameB) {
        return -1;
      }
      if (nameA > nameB) {
        return 1;
      }

      return 0;
    });

    expenses.push({
      name: rowsWithExpenses[0].children[1].children[0].textContent,
      amount: sumExpenses(
        document.querySelectorAll(
          "." + rowsWithExpenses[0].children[4].className
        )
      ),
    });

    for (let i = 1; i <= rowsWithExpenses.length - 1; i++) {
      if (
        rowsWithExpenses[i].children[1].textContent !=
        rowsWithExpenses[i - 1].children[1].textContent
      ) {
        expenses.push({
          name: rowsWithExpenses[i].children[1].children[0].textContent,
          amount: sumExpenses(
            document.querySelectorAll(
              "." + rowsWithExpenses[i].children[4].className
            )
          ),
        });
      }
    }
  }

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

  const drawChartOfIncomes = () => {
    let data = new google.visualization.DataTable();
    data.addColumn("string", "Kategoria");
    data.addColumn("number", "Kwota");

    data.addRows(incomes.length);
    for (let i = 0; i < incomes.length; i++) {
      data.setCell(i, 0, incomes[i].name);
      data.setCell(i, 1, incomes[i].amount);
    }

    let options = { title: "Przychody", width: chartWidth, height: 400 };

    let chart = new google.visualization.PieChart(
      document.getElementById("piechart-incomes")
    );
    chart.draw(data, options);
  };

  const drawChartOfExpenses = () => {
    let data = new google.visualization.DataTable();
    data.addColumn("string", "Kategoria");
    data.addColumn("number", "Kwota");

    data.addRows(expenses.length);
    for (let i = 0; i < expenses.length; i++) {
      data.setCell(i, 0, expenses[i].name);
      data.setCell(i, 1, expenses[i].amount);
    }

    let options = { title: "Wydatki", width: chartWidth, height: 400 };

    let chart = new google.visualization.PieChart(
      document.getElementById("piechart-expenses")
    );
    chart.draw(data, options);
  };
  google.charts.setOnLoadCallback(drawChartOfIncomes);
  google.charts.setOnLoadCallback(drawChartOfExpenses);
}
