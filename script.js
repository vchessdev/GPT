const skills = [
  { name: "HTML", level: 90, color: "#ff7a2d" },
  { name: "CSS", level: 85, color: "#39b6ff" },
  { name: "JavaScript", level: 80, color: "#ffe35e" },
  { name: "PHP", level: 72, color: "#9aa0d7" },
  { name: "Python", level: 88, color: "#5ac4ff" }
];

const skillWrap = document.getElementById("skills");

skills.forEach((skill) => {
  const row = document.createElement("div");
  row.className = "skill-row";
  row.innerHTML = `
    <strong>${skill.name}</strong>
    <div class="bar"><div style="width:${skill.level}%; background:${skill.color};"></div></div>
    <span>${skill.level}%</span>
  `;
  skillWrap.appendChild(row);
});
