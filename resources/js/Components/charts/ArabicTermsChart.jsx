import React from "react";
import {
    BarChart,
    Bar,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    Legend,
    ResponsiveContainer,
    Cell,
} from "recharts";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { useLanguage } from "@/Contexts/LanguageContext";
import { Badge } from "@/Components/ui/badge";

const ArabicTermsChart = ({ data, currentTerm, currentResource }) => {
    const { t } = useLanguage();

    // Use only the exact data provided
    let chartData = [];
    if (data && data.length > 0) {
        chartData = data.map((item) => ({
            name:
                item.resource_name.length > 20
                    ? `${item.resource_name.substring(0, 20)}...`
                    : item.resource_name,
            fullName: item.resource_name,
            arabicTermsCount: item.arabic_terms ? item.arabic_terms.length : 0,
            arabicTerms: item.arabic_terms || [],
            resourceId: item.resource_id,
            isCurrentResource: item.resource_id === currentResource?.id,
        }));
    }

    // If no data, show empty state
    if (!data || data.length === 0) {
        return (
            <Card className="w-full">
                <CardHeader>
                    <CardTitle className="text-sm sm:text-base">
                        {t("charts.arabic_terms_by_resource")}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="text-center p-4 text-gray-500 dark:text-gray-400">
                        <p className="text-sm">
                            {t("charts.no_arabic_terms_found")}
                        </p>
                    </div>
                </CardContent>
            </Card>
        );
    }

    // Colors for the bars
    const COLORS = [
        "#0088FE",
        "#00C49F",
        "#FFBB28",
        "#FF8042",
        "#8884D8",
        "#82CA9D",
        "#FF6B6B",
        "#4ECDC4",
        "#FFA726",
        "#66BB6A",
    ];

    // Custom tooltip
    const CustomTooltip = ({ active, payload, label }) => {
        if (active && payload && payload.length) {
            const dataItem = payload[0].payload;
            return (
                <div className="bg-white dark:bg-gray-800 p-3 border border-gray-200 dark:border-gray-700 rounded shadow-sm max-w-xs">
                    <p className="font-medium text-gray-900 dark:text-white mb-2">
                        {dataItem.fullName}
                    </p>
                    <p className="text-sm text-gray-600 dark:text-gray-300 mb-2">
                        {t("charts.arabic_terms_count")}:{" "}
                        <span className="font-semibold">
                            {dataItem.arabicTermsCount}
                        </span>
                    </p>
                    {dataItem.isCurrentResource && (
                        <p className="text-xs text-blue-600 dark:text-blue-400 font-medium mb-2">
                            {t("charts.current_resource")}
                        </p>
                    )}
                    {currentTerm?.term_en && (
                        <p className="text-xs text-gray-500 dark:text-gray-400 mb-2">
                            {t("charts.english_term")}: {currentTerm.term_en}
                        </p>
                    )}
                    {dataItem.arabicTerms.length > 0 && (
                        <div className="mt-2">
                            <p className="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {t("charts.arabic_alternatives")}:
                            </p>
                            <div className="space-y-1 max-h-32 overflow-y-auto">
                                {dataItem.arabicTerms.map((term, index) => (
                                    <div
                                        key={index}
                                        className="text-xs text-gray-600 dark:text-gray-400 font-arabic text-right p-1 bg-gray-50 dark:bg-gray-700 rounded"
                                        dir="rtl"
                                    >
                                        {term}
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            );
        }
        return null;
    };

    return (
        <Card className="w-full">
            <CardHeader>
                <CardTitle className="text-sm sm:text-base">
                    {t("charts.arabic_terms_by_resource")}
                </CardTitle>
                <p className="text-xs text-gray-500 dark:text-gray-400">
                    {t("charts.arabic_terms_description")}
                </p>
            </CardHeader>
            <CardContent>
                <div className="h-64 sm:h-72 md:h-80">
                    <ResponsiveContainer width="100%" height="100%">
                        <BarChart
                            data={chartData}
                            margin={{
                                top: 5,
                                right: 30,
                                left: 20,
                                bottom: 5,
                            }}
                        >
                            <CartesianGrid
                                strokeDasharray="3 3"
                                stroke="#e5e7eb"
                            />
                            <XAxis
                                dataKey="name"
                                angle={-45}
                                textAnchor="end"
                                height={60}
                                tick={{ fontSize: 12 }}
                                stroke="#6b7280"
                            />
                            <YAxis
                                stroke="#6b7280"
                                tick={{ fontSize: 12 }}
                                label={{
                                    value: t("charts.arabic_terms"),
                                    angle: -90,
                                    position: "insideLeft",
                                    offset: -10,
                                    style: {
                                        textAnchor: "middle",
                                        fontSize: 12,
                                    },
                                }}
                            />
                            <Tooltip content={<CustomTooltip />} />
                            <Legend
                                verticalAlign="top"
                                height={36}
                                formatter={() => t("charts.arabic_terms_count")}
                            />
                            <Bar
                                dataKey="arabicTermsCount"
                                name={t("charts.arabic_terms")}
                                radius={[4, 4, 0, 0]}
                            >
                                {chartData.map((entry, index) => (
                                    <Cell
                                        key={`cell-${index}`}
                                        fill={COLORS[index % COLORS.length]}
                                    />
                                ))}
                            </Bar>
                        </BarChart>
                    </ResponsiveContainer>
                </div>

                {/* Resource details with Arabic terms */}
                <div className="mt-6">
                    <h4 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        {t("charts.arabic_terms_details")}
                    </h4>
                    <div className="space-y-4 max-h-96 overflow-y-auto">
                        {chartData.map((resource, index) => (
                            <div
                                key={resource.resourceId}
                                className={`p-3 rounded border ${
                                    resource.isCurrentResource
                                        ? "border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20"
                                        : "border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800"
                                }`}
                            >
                                <div className="flex justify-between items-start mb-2">
                                    <div>
                                        <h5 className="font-medium text-gray-900 dark:text-white">
                                            {resource.fullName}

                                        </h5>
                                        <p className="text-xs text-gray-500 dark:text-gray-400">
                                            {t("charts.arabic_terms")}:{" "}
                                            {resource.arabicTermsCount}
                                        </p>
                                    </div>
                                    {currentTerm?.term_en && (
                                        <div className="text-xs text-gray-600 dark:text-gray-400">
                                            {t("charts.english")}:{" "}
                                            <span className="font-medium">
                                                {currentTerm.term_en}
                                            </span>
                                        </div>
                                    )}
                                </div>

                                {resource.arabicTerms.length > 0 ? (
                                    <div className="mt-2">
                                        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                            {resource.arabicTerms.map(
                                                (term, termIndex) => (
                                                    <div
                                                        key={termIndex}
                                                        className="p-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded text-right"
                                                        dir="rtl"
                                                    >
                                                        <p className="text-sm font-arabic text-gray-900 dark:text-white">
                                                            {term}
                                                        </p>
                                                    </div>
                                                )
                                            )}
                                        </div>
                                    </div>
                                ) : (
                                    <div className="text-center p-3 text-gray-400 dark:text-gray-500 text-sm">
                                        {t("charts.no_arabic_term_available")}
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                </div>

                {/* Summary statistics */}
                <div className="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                    <div className="bg-gray-50 dark:bg-gray-800 p-3 rounded">
                        <p className="text-gray-500 dark:text-gray-400">
                            {t("charts.total_resources")}
                        </p>
                        <p className="text-lg font-semibold text-gray-900 dark:text-white">
                            {chartData.length}
                        </p>
                    </div>
                    <div className="bg-gray-50 dark:bg-gray-800 p-3 rounded">
                        <p className="text-gray-500 dark:text-gray-400">
                            {t("charts.total_arabic_terms")}
                        </p>
                        <p className="text-lg font-semibold text-gray-900 dark:text-white">
                            {chartData.reduce(
                                (sum, item) => sum + item.arabicTermsCount,
                                0
                            )}
                        </p>
                    </div>
                    <div className="bg-gray-50 dark:bg-gray-800 p-3 rounded">
                        <p className="text-gray-500 dark:text-gray-400">
                            {t("charts.top_resource")}
                        </p>
                        <p className="text-lg font-semibold text-gray-900 dark:text-white truncate">
                            {chartData[0]?.fullName || "-"}
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
};

export default ArabicTermsChart;
